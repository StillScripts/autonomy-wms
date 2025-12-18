<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Enums\ThirdPartyProvider;
use App\Models\ThirdPartyVariableValue;

class ThirdPartyVariableValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Organisation $organisation): void
    {
        // Test values from test account
        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'test_public_key',
            env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'test_secret_key',
            env('STRIPE_TEST_SECRET_KEY', 'sk_test_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'test_webhook_secret',
            env('STRIPE_TEST_WEBHOOK_SECRET', 'whsec_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'test_webhook_endpoint_id',
            env('STRIPE_TEST_WEBHOOK_ENDPOINT_ID', 'we_' . \Str::random(24))
        );

        // Live environment variables from live account
        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'public_key',
            // using test key for local dev
            env('STRIPE_PUBLIC_KEY', 'pk_live_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'secret_key',
            env('STRIPE_SECRET_KEY', 'sk_live_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'webhook_secret',
            env('STRIPE_WEBHOOK_SECRET', 'whsec_' . \Str::random(24))
        );

        $organisation->setThirdPartyVariableValue(
            ThirdPartyProvider::STRIPE,
            'webhook_endpoint_id',
            env('STRIPE_WEBHOOK_ENDPOINT_ID', 'we_' . \Str::random(24))
        );
    }
}
