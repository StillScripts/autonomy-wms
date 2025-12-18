<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\ContentBlock;
use App\Services\FileUploadService;

class PageController extends Controller
{
    use AuthorizesRequests;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the pages for a website.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);
        
        return Inertia::render('websites/pages/index', [
            'website' => $website,
            'pages' => $website->pages
        ]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create(Website $website): Response
    {
        $this->authorize('update', $website);

        $organisation = auth()->user()->currentOrganisation();
        $contentBlockTypes = $organisation->contentBlockTypes;
        $contentBlocks = $organisation->contentBlocks->toArray();
        
        return Inertia::render('websites/pages/create', [
            'website' => $website,
            'contentBlockTypes' => $contentBlockTypes,
            'contentBlocks' => $contentBlocks,
        ]);
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request, Website $website)
    {
        $this->authorize('update', $website);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'contentBlocks' => 'nullable|array',
                'contentBlocks.*.content_block_type_id' => 'required|exists:content_block_types,id',
                'contentBlocks.*.content_block_id' => 'required|exists:content_blocks,id',
            ]);

            $page = $website->pages()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
            
            if (isset($validated['contentBlocks'])) {
                Log::info('Attempting to create content blocks:', [
                    'number_of_blocks' => count($validated['contentBlocks'])
                ]);

                foreach ($validated['contentBlocks'] as $index => $blockData) {
                    try {
                        $contentBlock = ContentBlock::findOrFail($blockData['content_block_id']);
                        $page->attachContentBlock($contentBlock, $index);

                        Log::info('Content block attached successfully:', [
                            'block_index' => $index,
                            'block_id' => $contentBlock->id,
                            'block_type_id' => $contentBlock->content_block_type_id,
                            'page_id' => $page->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to attach content block:', [
                            'block_index' => $index,
                            'error' => $e->getMessage(),
                            'block_data' => $blockData
                        ]);
                        throw $e;
                    }
                }

                // Verify content blocks were attached
                $attachedBlocksCount = $page->contentBlocks()->count();
                Log::info('Content blocks attachment completed:', [
                    'page_id' => $page->id,
                    'expected_blocks' => count($validated['contentBlocks']),
                    'actual_blocks_attached' => $attachedBlocksCount
                ]);
            } else {
                Log::info('No content blocks provided for page:', [
                    'page_id' => $page->id
                ]);
            }

            // Load the relationships before redirecting
            $page->load('contentBlocks.blockType');
            
            Log::info('Final page state:', [
                'page_id' => $page->id,
                'content_blocks_count' => $page->contentBlocks->count(),
                'content_blocks' => $page->contentBlocks->toArray()
            ]);

            return to_route('websites.pages.edit', [$website, $page]);

        } catch (\Exception $e) {
            Log::error('Page creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to create page: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified page.
     */
    public function show(Website $website, Page $page)
    {
        $page->load('contentBlocks.blockType');
        
        return response()->json([
            'page' => $page,
        ]);
    }

    /**
     * Display the specified page as JSON.
     */
    public function showJson(Website $website, Page $page)
    {
        $page->load('contentBlocks.blockType');
        
        return response()->json([
            'page' => $page,
        ]);
    }

    public function edit(Website $website, Page $page)
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('view', $website);
        
        $page->load('contentBlocks.blockType');
        $pageContentBlocks = $page->contentBlocks->map(function ($contentBlock) {
            return [
                'content_block_type_id' => (string) $contentBlock->content_block_type_id,
                'content_block_id' => (string) $contentBlock->id
            ];
        })->toArray();
        $contentBlocks = $organisation->contentBlocks;
        $contentBlockTypes = $organisation->contentBlockTypes;
        
        return Inertia::render('websites/pages/edit', [
            'website' => $website,
            'page' => $page,
            'contentBlockTypes' => $contentBlockTypes,
            'contentBlocks' => $contentBlocks,
            'pageContentBlocks' => $pageContentBlocks
        ]);
    }

    public function update(Request $request, Website $website, Page $page)
    {
        $this->authorize('update', $website);

        try {
            // Use the exact same validation as store
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'contentBlocks' => 'nullable|array',
                'contentBlocks.*.content_block_type_id' => 'required|exists:content_block_types,id',
                'contentBlocks.*.content_block_id' => 'required|exists:content_blocks,id',
            ]);

            $page->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
            
            Log::info('Page updated:', [
                'page_id' => $page->id,
                'page_title' => $page->title
            ]);

            // First, detach all existing content blocks
            $page->contentBlocks()->detach();
            Log::info('Detached all existing content blocks:', [
                'page_id' => $page->id
            ]);

            // Handle content blocks if provided
            if (isset($validated['contentBlocks'])) {
                Log::info('Attempting to attach new content blocks:', [
                    'number_of_blocks' => count($validated['contentBlocks'])
                ]);

                foreach ($validated['contentBlocks'] as $index => $blockData) {
                    try {
                        $contentBlock = ContentBlock::findOrFail($blockData['content_block_id']);
                        $page->attachContentBlock($contentBlock, $index);

                        Log::info('Content block attached successfully:', [
                            'block_index' => $index,
                            'block_id' => $contentBlock->id,
                            'block_type_id' => $contentBlock->content_block_type_id,
                            'page_id' => $page->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to attach content block:', [
                            'block_index' => $index,
                            'error' => $e->getMessage(),
                            'block_data' => $blockData
                        ]);
                        throw $e;
                    }
                }

                // Verify content blocks were attached
                $attachedBlocksCount = $page->contentBlocks()->count();
                Log::info('Content blocks attachment completed:', [
                    'page_id' => $page->id,
                    'expected_blocks' => count($validated['contentBlocks']),
                    'actual_blocks_attached' => $attachedBlocksCount
                ]);
            } else {
                Log::info('No content blocks provided for page:', [
                    'page_id' => $page->id
                ]);
            }

            $page->load('contentBlocks.blockType');
            
            Log::info('Final page state:', [
                'page_id' => $page->id,
                'content_blocks_count' => $page->contentBlocks->count(),
                'content_blocks' => $page->contentBlocks->toArray()
            ]);

            return to_route('websites.pages.edit', [$website, $page]);

        } catch (\Exception $e) {
            Log::error('Page update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to update page: ' . $e->getMessage()]);
        }
    }

    public function destroy(Website $website, Page $page)
    {
        $page->delete();

        return redirect()->route('websites.pages.index', $website)
            ->with('success', 'Page deleted successfully.');
    }
} 