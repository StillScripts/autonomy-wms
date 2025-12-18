<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\Website;

class WebsiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Organisation $organisation): array
    {
        $websites = [
            [
                'title' => 'My Portfolio',
                'domain' => 'portfolio.test',
                'description' => 'My professional portfolio website',
                'logo' => 'website-logos/3/bzeDylPdABiNR2xzqZGiBZCYPhkyJcJlJcu52npk.png',
            ],
            [
                'title' => 'My Blog',
                'domain' => 'blog.test',
                'description' => 'Personal blog about tech and development',
                'logo' => 'website-logos/3/bzeDylPdABiNR2xzqZGiBZCYPhkyJcJlJcu52npk.png',
            ],
            [
                'title' => 'Audio Zone',
                'domain' => 'audiozone.test',
                'description' => 'Audio Zone is a website for audio books',
                'logo' => 'website-logos/3/bzeDylPdABiNR2xzqZGiBZCYPhkyJcJlJcu52npk.png',
            ],
        ];        

        $createdWebsites = [];
        foreach ($websites as $websiteData) {
            $createdWebsites[] = Website::factory()->create([
                'organisation_id' => $organisation->id,
                'status' => 'active',
                ...$websiteData
            ]);
        }
        return $createdWebsites;
    }
}
