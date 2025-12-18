<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Website $website, array $contentBlocks, Website $audioZoneWebsite): void
    {
        $organisation = $website->organisation;
        [$sectionHeadingBlock, $faqSectionBlock, $websiteBlock, $bookCollectionBlock, $authorBlock, $heroSectionBlock, $articleBlock, $testimonialBlock, $freeSampleSectionBlock, $pricingSectionBlock, $testimonialsSectionBlock, $biographyBlock] = $contentBlocks;

        $homePage = Page::factory()->create([
            'website_id' => $website->id,
            'title' => 'Home',
            'slug' => 'home',
            'description' => 'Welcome to my portfolio',
        ]);

        // Attach the block to the home page
        $homePage->attachContentBlock($sectionHeadingBlock, 0);
        $homePage->attachContentBlock($faqSectionBlock, 1);
        
        $aboutPage = Page::factory()->create([
            'website_id' => $website->id,
            'title' => 'About',
            'slug' => 'about',
            'description' => 'Learn more about me',
        ]);

        // Attach the block to the about page
        $aboutPage->attachContentBlock($websiteBlock, 0);

        // Create home page for "Audio Zone" website with Lord of the Rings theme
        $audioZoneHomePage = Page::factory()->create([
            'website_id' => $audioZoneWebsite->id,
            'title' => 'Home',
            'slug' => 'home',
            'description' => 'Explore the Lord of the Rings book series.',
        ]);

        // Attach all content blocks to the home page in the correct order
        $audioZoneHomePage->attachContentBlock($heroSectionBlock, 0);
        $audioZoneHomePage->attachContentBlock($articleBlock, 1);
        $audioZoneHomePage->attachContentBlock($testimonialBlock, 2);
        $audioZoneHomePage->attachContentBlock($freeSampleSectionBlock, 3);
        $audioZoneHomePage->attachContentBlock($pricingSectionBlock, 4);
        $audioZoneHomePage->attachContentBlock($testimonialsSectionBlock, 5);
        $audioZoneHomePage->attachContentBlock($biographyBlock, 6);

        $audioBookCollectionPage = Page::factory()->create([
            'website_id' => $audioZoneWebsite->id,
            'title' => 'Audiobooks',
            'slug' => 'audiobooks',
            'description' => 'Audiobooks for Audio Zone',
        ]);        
    }
}
