<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\PageIdea;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PageIdeaController extends Controller
{
    public function __construct(
        private AIService $aiService
    ) {
        //
    }

    /**
     * Display a listing of the page ideas.
     */
    public function index(): Response
    {
        $pageIdeas = PageIdea::whereHas('messages', function ($query) {
            $query->whereHas('conversation', function ($conversationQuery) {
                $conversationQuery->where('user_id', Auth::id());
            });
        })
        ->with(['messages.conversation' => function ($query) {
            $query->select('id', 'title', 'created_at');
        }])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($pageIdea) {
            $firstMessage = $pageIdea->messages()->first();
            $conversation = $firstMessage?->conversation;
            
            if (!$conversation) {
                return null; // Skip page ideas without conversations
            }
            
            return [
                'id' => $pageIdea->id,
                'title' => $pageIdea->title,
                'summary' => $pageIdea->summary,
                'created_at' => $pageIdea->created_at,
                'updated_at' => $pageIdea->updated_at,
                'version_number' => $pageIdea->getVersionNumber(),
                'is_latest_version' => $pageIdea->isLatestVersion(),
                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'created_at' => $conversation->created_at,
                ],
            ];
        })
        ->filter() // Remove null values
        ->values(); // Re-index array

        return Inertia::render('page-ideas/index', [
            'pageIdeas' => $pageIdeas,
        ]);
    }

    /**
     * Show the page idea generation interface.
     */
    public function create(): Response
    {
        $conversation = Conversation::where('user_id', Auth::id())
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->first();

        if (!$conversation) {
            $currentOrganisation = Auth::user()->currentOrganisation();
            
            if (!$currentOrganisation) {
                abort(403, 'No organisation selected');
            }
            
            $conversation = Conversation::create([
                'title' => 'New Landing Page Idea',
                'user_id' => Auth::id(),
                'organisation_id' => $currentOrganisation->id,
            ]);
        }

        $latestPageIdea = $this->aiService->getLatestPageIdea($conversation);
        $pageIdeaVersions = $this->aiService->getPageIdeaVersions($conversation);

        return Inertia::render('page-ideas/create', [
            'conversation' => $conversation->load('messages'),
            'latestPageIdea' => $latestPageIdea,
            'pageIdeaVersions' => $pageIdeaVersions,
            'apiConnectionStatus' => $this->aiService->testConnection(),
        ]);
    }

    /**
     * Generate a new page idea.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:1000',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        // Check if user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $result = $this->aiService->generatePageIdea($conversation, $request->message);
            $pageIdea = $result['pageIdea'];

            // Redirect to the edit page for the newly created page idea
            return redirect()->route('page-ideas.edit', $pageIdea);

        } catch (\Exception $e) {
            throw $e; // Let Inertia handle the error
        }
    }

    /**
     * Show the edit form for a specific page idea.
     */
    public function edit(PageIdea $pageIdea): Response
    {
        // Check if user has access to this page idea
        $conversation = $pageIdea->conversation();
        
        if (!$conversation || $conversation->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('page-ideas/edit', [
            'pageIdea' => [
                'id' => $pageIdea->id,
                'title' => $pageIdea->title,
                'summary' => $pageIdea->summary,
                'sections' => $pageIdea->sections,
                'message' => $pageIdea->message,
                'created_at' => $pageIdea->created_at,
                'updated_at' => $pageIdea->updated_at,
                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'messages' => $conversation->messages()->orderBy('created_at')->get()->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'role' => $message->role,
                            'content' => $message->content,
                            'created_at' => $message->created_at,
                        ];
                    }),
                ],
            ],
            'apiConnectionStatus' => $this->aiService->testConnection(),
        ]);
    }

    /**
     * Show a specific page idea version.
     */
    public function show(PageIdea $pageIdea): Response
    {
        // Check if user has access to this page idea
        $conversation = $pageIdea->conversation();
        
        if (!$conversation || $conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $pageIdeaVersions = $this->aiService->getPageIdeaVersions($conversation);

        return Inertia::render('page-ideas/show', [
            'pageIdea' => $pageIdea,
            'conversation' => $conversation->load('messages'),
            'pageIdeaVersions' => $pageIdeaVersions,
        ]);
    }

    /**
     * Get all versions of page ideas for a conversation.
     */
    public function versions(Conversation $conversation): JsonResponse
    {
        // Check if user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $versions = $this->aiService->getPageIdeaVersions($conversation);

        return response()->json([
            'versions' => $versions,
        ]);
    }

    /**
     * Test the connection to the Ideas API.
     */
    public function testConnection(): JsonResponse
    {
        $isConnected = $this->aiService->testConnection();

        return response()->json([
            'connected' => $isConnected,
        ]);
    }
} 