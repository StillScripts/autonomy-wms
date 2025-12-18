<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\PageIdea;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class PageIdeaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first regular user (not admin) and their organisation
        $user = User::where('email', '!=', 'admin@example.com')->first();
        
        if (!$user) {
            $this->command->warn('No regular user found. Skipping page idea seeding.');
            return;
        }

        // Get the user's first organisation
        $organisation = $user->organisations()->first();
        
        if (!$organisation) {
            $this->command->warn('No organisation found for user. Skipping page idea seeding.');
            return;
        }

        // Create the conversation
        $conversation = Conversation::create([
            'title' => 'New Landing Page Idea',
            'user_id' => $user->id,
            'organisation_id' => $organisation->id,
        ]);

        // Create the first user message (car dealership FAQ)
        $userMessage1 = $conversation->messages()->create([
            'content' => 'create an faq page for car dealerships',
            'role' => 'user',
        ]);

        // Create the first assistant message (car dealership response)
        $assistantMessage1 = $conversation->messages()->create([
            'content' => 'Here\'s a structured plan for a comprehensive FAQ page tailored for a car dealership. Let me know if there\'s anything more specific you\'d like to add!',
            'role' => 'assistant',
        ]);

        // Create the second user message (gym membership request)
        $userMessage2 = $conversation->messages()->create([
            'content' => 'A page that appeals to young men that are open to signing up for a gym membership. The gym offers 20% off.',
            'role' => 'user',
        ]);

        // Create the first gym membership page idea
        $pageIdea1 = PageIdea::create([
            'title' => 'Gym Membership Landing Page',
            'summary' => 'A landing page targeting young men with a focus on a compelling 20% discount offer for gym memberships. It leverages visually appealing elements and powerful messaging to encourage sign-ups.',
            'sections' => [
                [
                    'title' => 'Hero Section',
                    'description' => 'Includes an eye-catching image of fit men working out, with a bold headline: \'Transform Your Body Today - Join Us and Save 20%!\'. A prominent call-to-action button: \'Claim Your Discount Now!\'',
                    'justification' => 'To immediately capture attention and highlight the core offer.',
                ],
                [
                    'title' => 'Benefits Section',
                    'description' => 'Showcases the benefits of joining the gym, such as state-of-the-art equipment, personal training options, and a vibrant community. Includes testimonials from young male members.',
                    'justification' => 'To reinforce the advantages and create a sense of value and community.',
                ],
                [
                    'title' => 'Discount Details',
                    'description' => 'Clear explanation of the 20% discount offer, including terms and conditions. Encourages urgency with language like \'Limited Time Offer\'.',
                    'justification' => 'To clarify the offer and push for swift decision-making.',
                ],
                [
                    'title' => 'Visual Gallery',
                    'description' => 'A collection of dynamic images showcasing the gym facilities, classes, and events, with captions emphasizing the gym\'s appeal and modernity.',
                    'justification' => 'To visually engage and show the gym\'s vibrant atmosphere.',
                ],
                [
                    'title' => 'Sign-Up CTA',
                    'description' => 'A final call-to-action section with a sign-up form, emphasizing the ease and speed of joining. Includes a secondary CTA like \'Take a Tour First!\'.',
                    'justification' => 'To convert visitors into leads and provide options based on their readiness to commit.',
                ],
            ],
            'message' => 'Here\'s a plan for a landing page to promote gym memberships with a focus on appealing to young men. Each section is designed to engage and convert visitors by highlighting the discount and the unique value your gym offers. Let me know if you\'d like to tweak any part of this plan!',
        ]);

        // Create the second assistant message and associate it with the first page idea
        $assistantMessage2 = $conversation->messages()->create([
            'content' => 'Here\'s a plan for a landing page to promote gym memberships with a focus on appealing to young men. Each section is designed to engage and convert visitors by highlighting the discount and the unique value your gym offers. Let me know if you\'d like to tweak any part of this plan!',
            'role' => 'assistant',
        ]);

        // Associate the first page idea with the assistant message
        $assistantMessage2->object()->associate($pageIdea1);
        $assistantMessage2->save();

        // Create the third user message (feedback)
        $userMessage3 = $conversation->messages()->create([
            'content' => 'I have reviewed the landing page idea. Please generate a new version based on my feedback.

The following changes are needed:
1. The Hero Section should focus on the discount offer more prominently
2. Add a testimonials section with text-based testimonials
3. Include a lifestyle section that showcases the gym community
4. Make the benefits section more specific about what makes this gym unique',
            'role' => 'user',
        ]);

        // Create the second gym membership page idea (updated version)
        $pageIdea2 = PageIdea::create([
            'title' => 'Gym Membership Landing Page for Young Men',
            'summary' => 'A targeted landing page to encourage young men to sign up for a gym membership with a 20% discount.',
            'sections' => [
                [
                    'title' => 'Hero Section',
                    'description' => 'The hero section immediately captures attention with a dynamic headline and a compelling subheading highlighting the 20% discount.',
                    'justification' => 'To capture the interest of young men right away and entice them with the discount offer.',
                ],
                [
                    'title' => 'Benefits Section',
                    'description' => 'Showcases the unique features and benefits of the gym, such as state-of-the-art equipment, experienced trainers, and a motivating community.',
                    'justification' => 'To clearly communicate why the gym is the best choice, beyond just price.',
                ],
                [
                    'title' => 'Discount Details',
                    'description' => 'Details the 20% discount offer, including any terms and conditions, to make the offer transparent and compelling.',
                    'justification' => 'To create urgency and entice potential members with a valuable offer.',
                ],
                [
                    'title' => 'Testimonials Section',
                    'description' => 'Showcases text-based testimonials from satisfied members, focusing on common goals young men might have (e.g., building muscle, increasing strength).',
                    'justification' => 'To establish trust and provide social proof that resonates with the target demographic.',
                ],
                [
                    'title' => 'Lifestyle Section',
                    'description' => 'A brief narrative or infographic showcasing the lifestyle and community of the gym, emphasizing camaraderie and support.',
                    'justification' => 'To engage those interested in the social aspects of joining a gym.',
                ],
                [
                    'title' => 'Sign-Up CTA',
                    'description' => 'A prominent call-to-action button that encourages visitors to sign up and claim the discount, with a simple form for them to fill out.',
                    'justification' => 'To convert page visitors into members by making sign-up quick and easy.',
                ],
            ],
            'message' => 'Here\'s an updated version of the landing page based on your feedback. The Hero Section now focuses on the discount offer more prominently, and I\'ve added the testimonials and lifestyle sections you requested. The benefits section has been made more specific about what makes this gym unique.',
        ]);

        // Create the third assistant message and associate it with the second page idea
        $assistantMessage3 = $conversation->messages()->create([
            'content' => 'Here\'s an updated version of the landing page based on your feedback. The Hero Section now focuses on the discount offer more prominently, and I\'ve added the testimonials and lifestyle sections you requested. The benefits section has been made more specific about what makes this gym unique.',
            'role' => 'assistant',
        ]);

        // Associate the second page idea with the assistant message
        $assistantMessage3->object()->associate($pageIdea2);
        $assistantMessage3->save();

        if ($this->command) {
            $this->command->info('Page ideas seeded successfully!');
            $this->command->info('Conversation ID: ' . $conversation->id);
            $this->command->info('Page Idea 1 ID: ' . $pageIdea1->id);
            $this->command->info('Page Idea 2 ID: ' . $pageIdea2->id);
        }
    }
} 