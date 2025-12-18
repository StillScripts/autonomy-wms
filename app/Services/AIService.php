<?php

namespace App\Services;

use App\Models\Message;
use App\Models\PageIdea;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private IdeasApiService $ideasApiService
    ) {
        //
    }

    /**
     * Generate a new page idea based on conversation messages.
     *
     * @param Conversation $conversation
     * @param string $userMessage
     * @return array{message: Message, pageIdea: PageIdea}
     * @throws \Exception
     */
    public function generatePageIdea(Conversation $conversation, string $userMessage): array
    {
        // Add the user message to the conversation
        $userMessageModel = $conversation->messages()->create([
            'content' => $userMessage,
            'role' => 'user',
        ]);

        // Prepare messages for the API
        $messages = $this->prepareMessagesForApi($conversation);

        try {
            // Generate the page idea using the Ideas API
            $apiResponse = $this->ideasApiService->generateLandingPageIdea($messages);

            // Create the page idea
            $pageIdea = PageIdea::create([
                'title' => $apiResponse['title'],
                'summary' => $apiResponse['summary'],
                'sections' => $apiResponse['sections'],
                'message' => $apiResponse['message'],
            ]);

            // Create the assistant message
            $assistantMessage = $conversation->messages()->create([
                'content' => $apiResponse['message'],
                'role' => 'assistant',
            ]);

            // Associate the page idea with the assistant message
            $assistantMessage->object()->associate($pageIdea);
            $assistantMessage->save();

            return [
                'message' => $assistantMessage,
                'pageIdea' => $pageIdea,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate page idea', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Prepare conversation messages for the Ideas API.
     *
     * @param Conversation $conversation
     * @return array<array{role: string, content: string}>
     */
    private function prepareMessagesForApi(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function (Message $message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            })
            ->toArray();
    }

    /**
     * Get the latest page idea for a conversation.
     *
     * @param Conversation $conversation
     * @return PageIdea|null
     */
    public function getLatestPageIdea(Conversation $conversation): ?PageIdea
    {
        return $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->orderBy('created_at', 'desc')
            ->first()
            ?->object;
    }

    /**
     * Get all page ideas for a conversation (for versioning).
     *
     * @param Conversation $conversation
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPageIdeaVersions(Conversation $conversation)
    {
        return $conversation->messages()
            ->where('role', 'assistant')
            ->whereNotNull('object_id')
            ->where('object_type', PageIdea::class)
            ->with('object')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('object');
    }

    /**
     * Test the connection to the Ideas API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->ideasApiService->testConnection();
    }
}
