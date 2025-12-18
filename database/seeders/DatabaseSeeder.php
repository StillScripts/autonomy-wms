<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Page;
use App\Models\Website;
use App\Models\Organisation;
use App\Models\Membership;
use App\Models\ContentBlockType;
use App\Models\ContentBlock;
use App\Enums\ThirdPartyProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Database\Seeders\ContentBlockTypeSeeder;
use Database\Seeders\ContentBlockSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\WebsiteSeeder;
use Database\Seeders\ThirdPartyVariableValueSeeder;
use Database\Seeders\ProductTypeSeeder;
use Database\Seeders\StripeProductSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\PageIdeaSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the admin user, owner of the super organisation
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        $superOrganisation = Organisation::firstOrCreate(
            ['name' => 'Autonomy Team'],
            ['is_super_org' => true, 'personal_organisation' => false]
        );

        // Sync the admin user to the super organisation if not already attached
        if (!$superOrganisation->users()->where('user_id', $adminUser->id)->exists()) {
            $superOrganisation->users()->attach($adminUser, [
                'role' => Membership::ROLE_OWNER
            ]);
        }

        $adminPersonalOrg = Organisation::firstOrCreate(
            ['name' => 'Admin User\'s Organisation'],
            ['personal_organisation' => true]
        );

        if (!$adminPersonalOrg->users()->where('user_id', $adminUser->id)->exists()) {
            $adminUser->organisations()->attach($adminPersonalOrg, [
                'role' => Membership::ROLE_OWNER
            ]);
        }

        // Create the default user, a customer with their own basic organisation
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        
        $organisation = Organisation::firstOrCreate(
            ['name' => 'Test User\'s Organisation'],
            ['personal_organisation' => true]
        );

        if (!$organisation->users()->where('user_id', $user->id)->exists()) {
            $user->organisations()->attach($organisation, [
                'role' => Membership::ROLE_OWNER
            ]);
        }

        $websites = app(WebsiteSeeder::class)->run($organisation);

        $website = $websites[0];

        $audioZoneWebsite = Website::where('title', 'Audio Zone')->first();

        $contentBlockTypes = app(ContentBlockTypeSeeder::class)->run($organisation);
        $contentBlocks = app(ContentBlockSeeder::class)->run($organisation, $website, $contentBlockTypes);

        app(PageSeeder::class)->run($website, $contentBlocks, $audioZoneWebsite);

        app(GlobalContentBlockSeeder::class)->run($websites, $contentBlocks);

        // Seed third party variable values
        app(ThirdPartyVariableValueSeeder::class)->run($organisation);

        app(ProductTypeSeeder::class)->run();

        $fellowshipProduct = app(StripeProductSeeder::class)->run();

        app(CustomerSeeder::class)->run($fellowshipProduct);

        app(PageIdeaSeeder::class)->run();

        // Output the login credentials
        $this->command->info('Test user created successfully!');
        $this->command->info('Login with:');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: password123');

        $this->command->info('Admin user created successfully!');
        $this->command->info('Login with:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password123');
    }
}
