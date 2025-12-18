<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Page;
use App\Models\GlobalContentBlock;

class GlobalContentBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(array $websites, array $contentBlocks): void
    {
        // Attach author and book collection to the audioZone website 
        $audioZoneWebsite = $websites[2];

        $authorBlock = $contentBlocks[4];
        $bookCollectionBlock = $contentBlocks[3];

        GlobalContentBlock::create([
            'website_id' => $audioZoneWebsite->id,
            'content_block_id' => $authorBlock->id,
        ]);

        GlobalContentBlock::create([
            'website_id' => $audioZoneWebsite->id,
            'content_block_id' => $bookCollectionBlock->id,
        ]);
    }
}
