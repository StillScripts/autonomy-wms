<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'organisation_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Define role constants
     */
    public const ROLE_OWNER = 'owner'; // Created the organisation
    public const ROLE_ADMIN = 'admin'; // Has access to all the organisation's data
    public const ROLE_MEMBER = 'member'; // Has access to some of the organisation's data

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => 'string',
    ];

    /**
     * Get the organisation that owns the membership.
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Get the user that owns the membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the user has the given role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Determine if the user is an owner.
     */
    public function isOwner(): bool
    {
        return $this->hasRole(self::ROLE_OWNER);
    }

    /**
     * Determine if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Determine if the user is a member.
     */
    public function isMember(): bool
    {
        return $this->hasRole(self::ROLE_MEMBER);
    }
} 