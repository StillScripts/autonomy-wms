<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function __construct(
        private AIService $aiService
    ) {}

    /**
     * Create a new conversation.
     */
    public function store(): JsonResponse
    {
        $user = auth()->user();
        $organisation = $user->currentOrganisation();

        if (!$organisation) {
            return response()->json(['error' => 'No organisation found'], 422);
        }

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'organisation_id' => $organisation->id,
            'title' => 'New Conversation'
        ]);

        return response()->json($conversation);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        // Create user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => $request->content,
            'role' => 'user'
        ]);

        // Get AI response
        $aiResponse = $this->aiService->processResponse([]);
        $aiResponse->conversation_id = $conversation->id;
        $aiResponse->save();

        return response()->json([
            'user_message' => $userMessage,
            'ai_response' => $aiResponse
        ]);
    }

    /**
     * Get all messages in a conversation.
     */
    public function getMessages(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()
            ->with('object')
            ->get();

        return response()->json($messages);
    }
}
