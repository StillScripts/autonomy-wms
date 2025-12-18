<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContentBlockType;
use App\Models\Organisation;

class ContentBlockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Organisation $organisation): array
    {
        $faqBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Faq',
            'slug' => 'faq',
            'fields' => [
                ['label' => 'Question', 'type' => 'text'],
                ['label' => 'Answer', 'type' => 'textarea'],
            ],
        ]);

        $faqSectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Faq Section',
            'slug' => 'faq-section',
            'fields' => [
                ['label' => 'Heading', 'type' => 'text'],
                ['label' => 'Faqs', 'type' => $faqBlockType->id],
            ],
        ]);

        $testimonialBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Testimonial',
            'slug' => 'testimonial',
            'fields' => [
                ['label' => 'Testimonial', 'type' => 'textarea'],
                ['label' => 'Name', 'type' => 'text'],
                ['label' => 'Company', 'type' => 'text'],
                ['label' => 'Image', 'type' => 'file'],
            ],
        ]);

        $sectionHeadingBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Section Heading',
            'slug' => 'section-heading',
            'fields' => [
                ['label' => 'Heading', 'type' => 'text'],
                ['label' => 'Subheading', 'type' => 'textarea'],
            ],
        ]);

        $authorBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Author',
            'slug' => 'author',
            'fields' => [
                ['label' => 'Name', 'type' => 'text'],
                ['label' => 'Introduction', 'type' => 'textarea'],
                ['label' => 'Summary', 'type' => 'textarea'],
                ['label' => 'Profile Image', 'type' => 'file'],
                ['label' => 'URL', 'type' => 'url'],
            ],
        ]);

        $bookCollectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Book Collection',
            'slug' => 'book-collection',
            'fields' => [
                ['label' => 'Image', 'type' => 'file'],
                ['label' => 'Series', 'type' => 'text'],
                ['label' => 'Description', 'type' => 'textarea'],
                ['label' => 'About The Series', 'type' => "richtext"],
            ],
        ]);

        // Create a simple link block type for hero section links
        $linkBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Link',
            'slug' => 'link',
            'fields' => [
                ['label' => 'Label', 'type' => 'text'],
                ['label' => 'Href', 'type' => 'url'],
            ],
        ]);

        // Create a testimonial item block type for hero section testimonials
        $testimonialItemBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Testimonial Item',
            'slug' => 'testimonial-item',
            'fields' => [
                ['label' => 'Name', 'type' => 'text'],
                ['label' => 'Bio', 'type' => 'text'],
                ['label' => 'Testimony', 'type' => 'textarea'],
                ['label' => 'Profile Image', 'type' => 'file'],
            ],
        ]);

        // Create a heading item block type for sections
        $headingItemBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Heading Item',
            'slug' => 'heading-item',
            'fields' => [
                ['label' => 'Heading', 'type' => 'text'],
                ['label' => 'Subheading', 'type' => 'textarea'],
                ['label' => 'Tagline', 'type' => 'text'],
            ],
        ]);

        // New content block types for "We Are The Earth Rising" website
        $heroSectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Hero Section',
            'slug' => 'hero-section',
            'fields' => [
                ['label' => 'Heading', 'type' => $headingItemBlockType->id],
                ['label' => 'Links', 'type' => $linkBlockType->id],
                ['label' => 'Testimonial', 'type' => $testimonialItemBlockType->id],
                ['label' => 'Banner Image', 'type' => 'file'],
            ],
        ]);

        $articleBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Article',
            'slug' => 'article',
            'fields' => [
                ['label' => 'Rich Text', 'type' => 'richtext'],
            ],
        ]);

        $freeSampleSectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Free Sample Section',
            'slug' => 'free-sample-section',
            'fields' => [
                ['label' => 'Heading', 'type' => 'text'],
                ['label' => 'Subheading', 'type' => 'textarea'],
                ['label' => 'Call To Action', 'type' => 'text'],
                ['label' => 'Button Text', 'type' => 'text'],
            ],
        ]);

        $pricingSectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Pricing Section',
            'slug' => 'pricing-section',
            'fields' => [
                ['label' => 'Heading', 'type' => $headingItemBlockType->id],
            ],
        ]);

        $testimonialsSectionBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Testimonials Section',
            'slug' => 'testimonials-section',
            'fields' => [
                ['label' => 'Heading', 'type' => $headingItemBlockType->id],
                ['label' => 'Testimonials', 'type' => $testimonialBlockType->id],
            ],
        ]);

        $biographyBlockType = ContentBlockType::factory()->forOrganisation($organisation->id)->create([
            'name' => 'Biography',
            'slug' => 'biography',
            'fields' => [
                ['label' => 'Name', 'type' => 'text'],
                ['label' => 'Introduction', 'type' => 'textarea'],
                ['label' => 'Summary', 'type' => 'textarea'],
                ['label' => 'Profile Image', 'type' => 'file'],
                ['label' => 'URL', 'type' => 'url'],
            ],
        ]);

        return [
            $faqBlockType,
            $faqSectionBlockType,
            $testimonialBlockType,
            $sectionHeadingBlockType,
            $authorBlockType,
            $bookCollectionBlockType,
            $heroSectionBlockType,
            $articleBlockType,
            $freeSampleSectionBlockType,
            $pricingSectionBlockType,
            $testimonialsSectionBlockType,
            $biographyBlockType,
        ];
    }
}
