<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasCurrentOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasCurrentOrganisation;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class)
                    ->using(Membership::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Check if the user is a super organization admin or owner.
     */
    public function isSuperOrgAdmin(): bool
    {
        return $this->organisations()
            ->where('is_super_org', true)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Get organisations that both this user and another user belong to.
     *
     * @param User $otherUser
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverlappingOrganisations(User $otherUser)
    {
        return $this->organisations()
            ->whereIn('organisations.id', $otherUser->organisations()->pluck('organisations.id'))
            ->get();
    }
}
