<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebsiteContentResource;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebsiteContentController extends Controller
{
    /**
     * Get all content for a website, including pages and global content blocks.
     */
    public function index(Website $website): AnonymousResourceCollection
    {
        $website->load([
            'pages.contentBlocks',
            'globalContentBlocks.contentBlock'
        ]);

        return WebsiteContentResource::collection([$website]);
    }

    /**
     * Get content for a specific page, including global content blocks.
     */
    public function show(Website $website, string $pageSlug): WebsiteContentResource
    {
        $website->load([
            'pages' => function ($query) use ($pageSlug) {
                $query->where('slug', $pageSlug);
            },
            'pages.contentBlocks',
            'globalContentBlocks.contentBlock'
        ]);

        return new WebsiteContentResource($website);
    }

    /**
     * Get the global content for a website
     */
    public function globalContent(Website $website): AnonymousResourceCollection
    {
        $website->load([
            'globalContentBlocks.contentBlock'
        ]);

        return WebsiteContentResource::collection([$website]);
    }
}
