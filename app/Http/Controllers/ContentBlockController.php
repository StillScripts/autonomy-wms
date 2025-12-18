<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\ContentBlockType;
use App\Services\FileUploadService;
use App\Http\Requests\ContentBlockRequest;
class ContentBlockController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the content blocks.
     */
    public function index(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('viewAny', ContentBlock::class);
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        return Inertia::render('content-blocks/index', [
            'contentBlocks' => ContentBlock::where('organisation_id', $organisation->id)
                ->with(['blockType', 'website'])
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new content block.
     */
    public function create(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('create', [ContentBlock::class, $organisation]);
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        return Inertia::render('content-blocks/create', [
            'contentBlockTypes' => $organisation->contentBlockTypes,
            'websites' => $organisation->websites,
        ]);
    }

    /**
     * Store a newly created content block in storage.
     */
    public function store(ContentBlockRequest $request)
    {
        try {
            $organisation = auth()->user()->currentOrganisation();
            $this->authorize('create', [ContentBlock::class, $organisation]);
            
            $validated = $request->validated();
            $validated['organisation_id'] = $organisation->id;

            $contentBlock = ContentBlock::create($validated);

            return to_route('content-blocks.show', $contentBlock)
                ->with('success', 'Content block created successfully.');

        } catch (\Exception $e) {           
            return back()->withErrors(['error' => 'Failed to create content block: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified content block.
     */
    public function show(ContentBlock $contentBlock): Response
    {
        $this->authorize('view', $contentBlock);

        return Inertia::render('content-blocks/show', [
            'contentBlock' => $contentBlock->load(['blockType', 'website']),
        ]);
    }

    /**
     * Show the form for editing the specified content block.
     */
    public function edit(ContentBlock $contentBlock): Response
    {

        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }       
        $this->authorize('update', $contentBlock);

        return Inertia::render('content-blocks/edit', [
            'contentBlock' => $contentBlock->load(['blockType', 'website']),
            'contentBlockTypes' => $organisation->contentBlockTypes,
        ]);
    }

    /**
     * Update the specified content block in storage.
     */
    public function update(ContentBlockRequest $request, ContentBlock $contentBlock)
    {
        $this->authorize('update', $contentBlock);

        try {
            $validated = $request->validated();
            $contentBlock->update($validated);

            return to_route('content-blocks.show', $contentBlock)
                ->with('success', 'Content block updated successfully.');

        } catch (\Exception $e) {          
            return back()->withErrors(['error' => 'Failed to update content block: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified content block from storage.
     */
    public function destroy(ContentBlock $contentBlock)
    {
        $this->authorize('delete', $contentBlock);

        try {
            $contentBlock->delete();

            return to_route('content-blocks.index')
                ->with('success', 'Content block deleted successfully.');

        } catch (\Exception $e) {          
            return back()->withErrors(['error' => 'Failed to delete content block: ' . $e->getMessage()]);
        }
    }
} 