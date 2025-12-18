<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\ContentBlockType;
use App\Models\ContentBlock;
use App\Models\Website;
class ContentBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Organisation $organisation, Website $website, array $contentBlockTypes): array
    {
        [$faqBlockType, $faqSectionBlockType, $testimonialBlockType, $sectionHeadingBlockType, $authorBlockType, $bookCollectionBlockType, $heroSectionBlockType, $articleBlockType, $freeSampleSectionBlockType, $pricingSectionBlockType, $testimonialsSectionBlockType, $biographyBlockType] = $contentBlockTypes;

        $sectionHeadingBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => null,
            'content_block_type_id' => $sectionHeadingBlockType->id,
            'content' => [
                'heading' => 'Exploring Winter Destinations',
                'subheading' => 'Step on an adventure and explore the world',
            ],
            'description' => 'This is a description for the organisation-wide content block',
        ]);

        $faqSectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => null, 
            'content_block_type_id' => $faqSectionBlockType->id,
            'content' => [
                'heading' => 'Frequently Asked Questions',
                'faqs' => [
                    [
                        'question' => 'What colour is the sky?',
                        'answer' => 'The sky is blue',
                    ],
                    [
                        'question' => 'What is the capital of France?',
                        'answer' => 'Paris',
                    ],
                ],
            ],
        ]);

        $bookCollectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => null,
            'description' => 'The Lord of the Rings book series',
            'content_block_type_id' => $bookCollectionBlockType->id,
            'content' => [
                'series' => 'Lord of the Rings',
                'description' => 'One of the most iconic fantasy series of all time',
                'image' => 'website-logos/3/Zjt8DL94BLXi3KCEPRsuDtV1mN6fUGpYoA5ZyhKg.jpg',
                'about_the_series' => 'The Lord of the Rings is an epic fantasy novel by J.R.R. Tolkien. It is the first volume of The Hobbit, and The Lord of the Rings trilogy.',
                'about-the-series' => [
                    'root' => [
                        'children' => [
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'I wrote \'The Lord of the Rings\' to create a mythology for England and to explore themes of friendship, courage, and the struggle against evil.',
                                        'type' => 'text',
                                        'version' => 1,
                                    ],
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textFormat' => 0,
                                'textStyle' => null,
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'It is an epic fantasy series about a group of companions from different races awakening to the potential to unite against darkness and it tracks the characters through a journey of self-discovery and trying to implement solutions in all areas of life – including friendship, loyalty, courage, and basically bringing hope back to Middle-earth.',
                                        'type' => 'text',
                                        'version' => 1,
                                    ],
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textFormat' => 0,
                                'textStyle' => null,
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'The solutions are already here, and the Series is intended to show how such solutions could proliferate from the ground up, just as natural systems do, by creating a fictional blueprint of that experience. The \'fictional unfolding\' is supported by over 150 links to research and commentary, providing detailed support for the themes in the story.',
                                        'type' => 'text',
                                        'version' => 1,
                                    ],
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textFormat' => 0,
                                'textStyle' => null,
                            ],
                        ],
                        'direction' => 'ltr',
                        'format' => null,
                        'indent' => 0,
                        'type' => 'root',
                        'version' => 1,
                    ],
                ],
            ],
        ]);

        $authorBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => null,
            'description' => 'The author of the Lord of the Rings',
            'content_block_type_id' => $authorBlockType->id,
            'content' => [
                'name' => 'J.R.R. Tolkien',
                'introduction' => 'John Ronald Reuel Tolkien was an English writer, poet, philologist, and university professor who is best known as the author of the classic high fantasy works The Hobbit, The Lord of the Rings, and The Silmarillion.',
                'summary' => 'John Ronald Reuel Tolkien was an English writer, poet, philologist, and university professor who is best known as the author of the classic high fantasy works The Hobbit, The Lord of the Rings, and The Silmarillion.',
                'profile_image' => 'https://via.placeholder.com/150',
                'url' => 'https://www.tolkiensociety.org/',
            ],
        ]);
                

        // Create a website-specific content block
        $websiteBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $website->id, // website-specific block
            'content_block_type_id' => $sectionHeadingBlockType->id,
            'content' => [
                'heading' => 'About',
                'subheading' => 'I am a software developer',
            ],
            'description' => 'This is a description for the website-specific content block',
        ]);

        // Get the "Audio Zone" website
        $audioZoneWebsite = Website::where('title', 'Audio Zone')->first();

        // Create content blocks for "Audio Zone" website with Lord of the Rings theme
        $heroSectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $heroSectionBlockType->id,
            'content' => [
                'heading' => [
                    [
                        'heading' => 'Get lost in the amazing world of Middle-earth.',
                        'subheading' => 'A book and audio series that takes you on an epic journey through the realms of fantasy.',
                        'tagline' => null
                    ]
                ],
                'links' => [
                    [
                        'label' => 'Get sample chapter',
                        'href' => '#'
                    ]
                ],
                'testimonial' => [
                    [
                        'name' => 'Gandalf the Grey',
                        'bio' => 'Wizard of Middle-earth',
                        'testimony' => '"This tale of the One Ring is truly magnificent. I wish I had known this story a lot sooner."',
                        'profile-image' => null
                    ]
                ],
                'banner-image' => 'content-blocks/9/FYjHYQPgsWr2DS1Rd9GcInMjBiK0h8ifWWUFeNBM.png',
            ],
        ]);

        $articleBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $articleBlockType->id,
            'content' => [
                'richtext' => [
                    'root' => [
                        'children' => [
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => '"The Lord of the Rings" is a book and audio series that takes you on an epic journey through the realms of fantasy that anyone can enjoy.',
                                        'type' => 'text',
                                        'version' => 1
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'heading',
                                'version' => 1,
                                'textStyle' => null,
                                'tag' => 'h2'
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'Before I discovered the world of Middle-earth, I always imagined that fantasy stories were simple tales of good versus evil, some sort of basic adventure, and hours and hours spent reading through endless descriptions.',
                                        'type' => 'text',
                                        'version' => 1
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textStyle' => null,
                                'textFormat' => 0
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'But it turns out this isn\'t how great fantasy authors work at all.',
                                        'type' => 'text',
                                        'version' => 1
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textStyle' => null,
                                'textFormat' => 0
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'In "The Lord of the Rings", you\'ll discover the systems experts use to create immersive fantasy worlds, without relying on simple tropes.',
                                        'type' => 'text',
                                        'version' => 1
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textStyle' => null,
                                'textFormat' => 0
                            ],
                            [
                                'children' => [
                                    [
                                        'children' => [
                                            [
                                                'detail' => 0,
                                                'format' => 0,
                                                'mode' => 'normal',
                                                'style' => null,
                                                'text' => '✔️ Using world-building to create complex and believable realms',
                                                'type' => 'text',
                                                'version' => 1
                                            ]
                                        ],
                                        'direction' => 'ltr',
                                        'format' => 'start',
                                        'indent' => 0,
                                        'type' => 'listitem',
                                        'version' => 1,
                                        'textStyle' => null,
                                        'value' => 1
                                    ],
                                    [
                                        'children' => [
                                            [
                                                'detail' => 0,
                                                'format' => 0,
                                                'mode' => 'normal',
                                                'style' => null,
                                                'text' => '✔️ How to develop characters with depth and growth',
                                                'type' => 'text',
                                                'version' => 1
                                            ]
                                        ],
                                        'direction' => 'ltr',
                                        'format' => 'start',
                                        'indent' => 0,
                                        'type' => 'listitem',
                                        'version' => 1,
                                        'textStyle' => null,
                                        'value' => 2
                                    ],
                                    [
                                        'children' => [
                                            [
                                                'detail' => 0,
                                                'format' => 0,
                                                'mode' => 'normal',
                                                'style' => null,
                                                'text' => '✔️ Creating languages and cultures that feel authentic',
                                                'type' => 'text',
                                                'version' => 1
                                            ]
                                        ],
                                        'direction' => 'ltr',
                                        'format' => 'start',
                                        'indent' => 0,
                                        'type' => 'listitem',
                                        'version' => 1,
                                        'textStyle' => null,
                                        'value' => 3
                                    ],
                                    [
                                        'children' => [
                                            [
                                                'detail' => 0,
                                                'format' => 0,
                                                'mode' => 'normal',
                                                'style' => null,
                                                'text' => '✔️ Identifying the characteristics that make a story timeless',
                                                'type' => 'text',
                                                'version' => 1
                                            ]
                                        ],
                                        'direction' => 'ltr',
                                        'format' => 'start',
                                        'indent' => 0,
                                        'type' => 'listitem',
                                        'version' => 1,
                                        'textStyle' => null,
                                        'value' => 4
                                    ],
                                    [
                                        'children' => [
                                            [
                                                'detail' => 0,
                                                'format' => 0,
                                                'mode' => 'normal',
                                                'style' => null,
                                                'text' => '✔️ Writing techniques and narrative structures to engage readers',
                                                'type' => 'text',
                                                'version' => 1
                                            ]
                                        ],
                                        'direction' => 'ltr',
                                        'format' => 'start',
                                        'indent' => 0,
                                        'type' => 'listitem',
                                        'version' => 1,
                                        'textStyle' => null,
                                        'value' => 5
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => null,
                                'indent' => 0,
                                'type' => 'list',
                                'version' => 1,
                                'textStyle' => null,
                                'listType' => 'bullet',
                                'start' => 1,
                                'tag' => 'ul'
                            ],
                            [
                                'children' => [
                                    [
                                        'detail' => 0,
                                        'format' => 0,
                                        'mode' => 'normal',
                                        'style' => null,
                                        'text' => 'By the end of the series, you\'ll have all the inspiration you need to dig in and start creating beautiful stories that can hold their own against any of the classics you can find in literature.',
                                        'type' => 'text',
                                        'version' => 1
                                    ]
                                ],
                                'direction' => 'ltr',
                                'format' => 'start',
                                'indent' => 0,
                                'type' => 'paragraph',
                                'version' => 1,
                                'textStyle' => null,
                                'textFormat' => 0
                            ]
                        ],
                        'direction' => 'ltr',
                        'format' => null,
                        'indent' => 0,
                        'type' => 'root',
                        'version' => 1,
                        'textStyle' => null
                    ]
                ]
            ],
        ]);

        $testimonialBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $testimonialBlockType->id,
            'content' => [
                'testimony' => '"I didn\'t know a thing about fantasy literature until I read this series. Now I can appreciate any fantasy story I encounter. Great resource!"',
                'name' => 'Frodo Baggins',
                'bio' => 'Ring-bearer',
                'profile-image' => 'content-blocks/4/r2ru8l9b3adh8a52fkSMhtRUnHA4zD76tKCYBo5m.png',
            ],
        ]);

        $freeSampleSectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $freeSampleSectionBlockType->id,
            'content' => [
                'heading' => 'Get the free sample chapters',
                'subheading' => 'Enter your email address and I\'ll send you a sample from the book containing two of my favorite chapters.',
                'call-to-action' => 'Get two free chapters straight to your inbox',
                'button-text' => 'Download Sample'
            ],
        ]);

        $pricingSectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $pricingSectionBlockType->id,
            'content' => [
                'heading' => [
                    [
                        'heading' => 'Pick your package',
                        'subheading' => '"The Lord of the Rings" is available in two different packages so you can pick the one that\'s right for you.',
                        'tagline' => null
                    ]
                ]
            ],
        ]);

        $testimonialsSectionBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $testimonialsSectionBlockType->id,
            'content' => [
                'heading' => [
                    [
                        'heading' => 'Some kind words from early readers...',
                        'subheading' => 'I worked with a small group of early access readers to make sure all of the content in the book was exactly what they needed. Here\'s what they had to say about the finished product.',
                        'tagline' => null
                    ]
                ],
                'testimonials' => [
                    [
                        'name' => 'Aragorn',
                        'bio' => 'Ranger of the North',
                        'testimony' => 'Tolkien\'s storytelling is second to none. Everything was easy to follow every step of the way.',
                        'profile-image' => []
                    ],
                    [
                        'name' => 'Legolas',
                        'bio' => 'Elf of Mirkwood',
                        'testimony' => 'I run a kingdom of elves and could never find a good story about our people. Now I can read about our own adventures in minutes.',
                        'profile-image' => []
                    ],
                    [
                        'name' => 'Gimli',
                        'bio' => 'Dwarf Warrior',
                        'testimony' => 'I couldn\'t believe how fast Tolkien moved through the story compared to my own reading. I\'m enjoying the tale more accurately in half the time with the techniques I learned from his writing.',
                        'profile-image' => []
                    ]
                ]
            ],
        ]);

        $biographyBlock = ContentBlock::factory()->create([
            'organisation_id' => $organisation->id,
            'website_id' => $audioZoneWebsite->id,
            'content_block_type_id' => $biographyBlockType->id,
            'content' => [
                'name' => 'J.R.R. Tolkien',
                'introduction' => 'Hey there, I\'m the author behind \'The Lord of the Rings\'.',
                'summary' => 'I\'ve been writing fantasy professionally for over a decade and have worked with dozens of the biggest publishers to create custom worlds for their readers. I\'m an accomplished academic, and have been teaching literature workshops every month for the last three years. I\'ve worked with readers of all skill levels and honed my way of writing to really click for anyone who has the itch to start reading their own fantasy stories.',
                'profile-image' => 'content-blocks/7/zlu5ygixsUlCc5VhuSQHu02iMGzeC2OcSukli2lR.png',
                'url' => 'https://www.tolkiensociety.org/'
            ],
        ]);

        return [
            $sectionHeadingBlock,
            $faqSectionBlock,
            $websiteBlock,
            $bookCollectionBlock,
            $authorBlock,
            $heroSectionBlock,
            $articleBlock,
            $testimonialBlock,
            $freeSampleSectionBlock,
            $pricingSectionBlock,
            $testimonialsSectionBlock,
            $biographyBlock,
        ];
    }
}
