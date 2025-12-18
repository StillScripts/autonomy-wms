<?php

namespace App\Http\Controllers;

use App\Models\GlobalContentBlock;
use App\Models\Website;
use App\Models\ContentBlock;
use App\Models\ContentBlockType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GlobalContentBlockController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for editing global content blocks for a website.
     */
    public function edit(Website $website): Response
    {
        $this->authorize('update', $website);
        
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        // Load existing global content blocks for this website
        $globalContentBlocks = $website->globalContentBlocks()
            ->with(['contentBlock.blockType'])
            ->get()
            ->map(function ($globalBlock) {
                return [
                    'content_block_type_id' => (string) $globalBlock->contentBlock->content_block_type_id,
                    'content_block_id' => (string) $globalBlock->contentBlock->id
                ];
            })
            ->toArray();

        // Get available content blocks for this organisation/website
        $contentBlocks = ContentBlock::where('organisation_id', $organisation->id)
            ->where(function ($query) use ($website) {
                $query->whereNull('website_id')
                      ->orWhere('website_id', $website->id);
            })
            ->with('blockType')
            ->get();

        $contentBlockTypes = $organisation->contentBlockTypes;

        return Inertia::render('websites/global-content-blocks/edit', [
            'website' => $website,
            'contentBlockTypes' => $contentBlockTypes,
            'contentBlocks' => $contentBlocks,
            'globalContentBlocks' => $globalContentBlocks
        ]);
    }

    /**
     * Update the global content blocks for a website.
     */
    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        try {
            $validated = $request->validate([
                'globalContentBlocks' => 'nullable|array',
                'globalContentBlocks.*.content_block_type_id' => 'required|exists:content_block_types,id',
                'globalContentBlocks.*.content_block_id' => 'required|exists:content_blocks,id',
            ]);

            Log::info('Updating global content blocks for website:', [
                'website_id' => $website->id,
                'global_blocks_count' => isset($validated['globalContentBlocks']) ? count($validated['globalContentBlocks']) : 0
            ]);

            // First, remove all existing global content blocks for this website
            $website->globalContentBlocks()->delete();
            Log::info('Removed all existing global content blocks for website:', [
                'website_id' => $website->id
            ]);

            // Add the new global content blocks
            if (isset($validated['globalContentBlocks'])) {
                foreach ($validated['globalContentBlocks'] as $index => $blockData) {
                    try {
                        $contentBlock = ContentBlock::findOrFail($blockData['content_block_id']);
                        
                        // Verify the content block belongs to the same organisation
                        if ($contentBlock->organisation_id !== auth()->user()->currentOrganisation()->id) {
                            throw new \Exception('Content block does not belong to the current organisation');
                        }

                        GlobalContentBlock::create([
                            'website_id' => $website->id,
                            'content_block_id' => $contentBlock->id,
                        ]);

                        Log::info('Global content block created successfully:', [
                            'website_id' => $website->id,
                            'content_block_id' => $contentBlock->id,
                            'index' => $index
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create global content block:', [
                            'website_id' => $website->id,
                            'block_data' => $blockData,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }
            }

            return to_route('websites.show', $website)
                ->with('success', 'Global content blocks updated successfully.');

        } catch (\Exception $e) {
            Log::error('Global content blocks update failed:', [
                'website_id' => $website->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to update global content blocks: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove a specific global content block from a website.
     */
    public function destroy(Website $website, GlobalContentBlock $globalContentBlock)
    {
        $this->authorize('update', $website);

        try {
            // Verify the global content block belongs to this website
            if ($globalContentBlock->website_id !== $website->id) {
                return back()->withErrors(['error' => 'Global content block does not belong to this website.']);
            }

            $globalContentBlock->delete();

            return back()->with('success', 'Global content block removed successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to remove global content block:', [
                'website_id' => $website->id,
                'global_content_block_id' => $globalContentBlock->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Failed to remove global content block: ' . $e->getMessage()]);
        }
    }
} 