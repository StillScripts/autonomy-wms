<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageIdea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'summary',
        'sections',
        'message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sections' => 'array',
    ];

    /**
     * Get the messages associated with this page idea.
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'object');
    }

    /**
     * Get the conversation that owns this page idea.
     */
    public function conversation(): ?Conversation
    {
        $firstMessage = $this->messages()->first();
        return $firstMessage?->conversation;
    }

    /**
     * Get the sections as a typed array.
     *
     * @return array<array{title: string, description: string, justification: string}>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Get the version number of this page idea within its conversation.
     *
     * @return int
     */
    public function getVersionNumber(): int
    {
        $conversation = $this->conversation();
        
        return $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->where('created_at', '<=', $this->created_at)
            ->count();
    }

    /**
     * Check if this is the latest version of the page idea.
     *
     * @return bool
     */
    public function isLatestVersion(): bool
    {
        $conversation = $this->conversation();
        
        $latestMessage = $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestMessage && $latestMessage->object_id === $this->id;
    }

    /**
     * Get the previous version of this page idea.
     *
     * @return PageIdea|null
     */
    public function getPreviousVersion(): ?PageIdea
    {
        $conversation = $this->conversation();
        
        $previousMessage = $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        return $previousMessage?->object;
    }

    /**
     * Get the next version of this page idea.
     *
     * @return PageIdea|null
     */
    public function getNextVersion(): ?PageIdea
    {
        $conversation = $this->conversation();
        
        $nextMessage = $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'asc')
            ->first();

        return $nextMessage?->object;
    }
}
