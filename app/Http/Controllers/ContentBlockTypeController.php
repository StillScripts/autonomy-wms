<?php

namespace App\Http\Controllers;

use App\Models\ContentBlockType;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use App\Http\Requests\ContentBlockTypeRequest;
class ContentBlockTypeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the content block types for the current organisation.
     */
    public function index(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('viewAny', ContentBlockType::class);

        return Inertia::render('content-block-types/index', [
            'organisation' => $organisation,
            'contentBlockTypes' => ContentBlockType::getOrganisationTypes($organisation)
        ]);
    }

    /**
     * Show the form for creating a new content block type.
     */
    public function create(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('create', [ContentBlockType::class, $organisation]);

        return Inertia::render('content-block-types/create', [
            'organisation' => $organisation,
            'customContentBlockTypeOptions' => ContentBlockType::getArrayFieldOptions($organisation),
        ]);
    }

    /**
     * Store a newly created content block type in storage.
     */
    public function store(ContentBlockTypeRequest $request)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('create', [ContentBlockType::class, $organisation]);

        try {
            $validated = $request->validated();
                    
            $slug = Str::slug($validated['name']);
            $exists = $organisation->contentBlockTypes()
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'error' => 'A content block type with this name already exists in your organisation.'
                ]);
            }

            $contentBlockType = $organisation->contentBlockTypes()->create($validated);

            return to_route('content-block-types.show', $contentBlockType);

        } catch (\Exception $e) {           
            return back()->withErrors(['error' => 'Failed to create content block type: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified content block type.
     */
    public function show(ContentBlockType $contentBlockType): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('view', $contentBlockType);

        return Inertia::render('content-block-types/show', [
            'organisation' => $organisation,
            'contentBlockType' => $contentBlockType,
            'customContentBlockTypeOptions' => ContentBlockType::getArrayFieldOptions($organisation),
        ]);
    }

    /**
     * Show the form for editing the specified content block type.
     */
    public function edit(ContentBlockType $contentBlockType): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('update', $contentBlockType);

        return Inertia::render('websites/pages/content-block-types/edit', [
            'organisation' => $organisation,
            'contentBlockType' => $contentBlockType,
        ]);
    }

    /**
     * Update the specified content block type in storage.
     */
    public function update(ContentBlockTypeRequest $request, ContentBlockType $contentBlockType)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('update', $contentBlockType);

        try {
            $validated = $request->validated();
            $contentBlockType->update($validated);

            return to_route('content-block-types.show', $contentBlockType)
                ->with('success', 'Content block type updated successfully.');

        } catch (\Exception $e) {           
            return back()->withErrors(['error' => 'Failed to update content block type: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified content block type from storage.
     */
    public function destroy(ContentBlockType $contentBlockType)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $this->authorize('delete', $contentBlockType);

        try {
            $contentBlockType->delete();

            return to_route('content-block-types.index')
                ->with('success', 'Content block type deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete content block type: ' . $e->getMessage()]);
        }
    }
} 