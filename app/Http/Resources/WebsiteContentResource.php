<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'domain' => $this->domain,
            'description' => $this->description,
            'pages' => $this->pages->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'description' => $page->description,
                    'content_blocks' => $page->contentBlocks->map(function ($block) {
                        return [
                            'id' => $block->id,
                            'type' => $block->blockType->name,
                            'content' => $block->content_with_urls,
                        ];
                    }),
                ];
            }),
            'global_content_blocks' => $this->globalContentBlocks->map(function ($globalBlock) {
                return [
                    'id' => $globalBlock->contentBlock->id,
                    'type' => $globalBlock->contentBlock->blockType->name,
                    'content' => $globalBlock->contentBlock->content_with_urls,
                ];
            }),
        ];
    }
}
