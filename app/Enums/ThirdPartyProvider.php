<?php

namespace App\Enums;

enum ThirdPartyProvider: string
{
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case MAILCHIMP = 'mailchimp';
    case BREVO = 'brevo';
    case MAILERLITE = 'mailerlite';
    case SENDGRID = 'sendgrid';
    case TWILIO = 'twilio';

    public function getVariables(): array
    {
        return match($this) {
            self::STRIPE => [
                'test_public_key' => [
                    'name' => 'STRIPE_TEST_PUBLIC_KEY',
                    'description' => 'Stripe test environment publishable key (starts with pk_test_)',
                    'is_secret' => false,
                    'is_test' => true,
                ],
                'test_secret_key' => [
                    'name' => 'STRIPE_TEST_SECRET_KEY',
                    'description' => 'Stripe test environment secret key (starts with sk_test_)',
                    'is_secret' => true,
                    'is_test' => true,
                ],
                'test_webhook_secret' => [
                    'name' => 'STRIPE_TEST_WEBHOOK_SECRET',
                    'description' => 'Stripe test environment webhook signing secret (starts with whsec_)',
                    'is_secret' => true,
                    'is_test' => true,
                ],
                'test_webhook_endpoint_id' => [
                    'name' => 'STRIPE_TEST_WEBHOOK_ENDPOINT_ID',
                    'description' => 'Stripe test environment webhook endpoint ID (starts with we_)',
                    'is_secret' => false,
                    'is_test' => true,
                ],
                'public_key' => [
                    'name' => 'STRIPE_PUBLIC_KEY',
                    'description' => 'Stripe live environment publishable key (starts with pk_live_)',
                    'is_secret' => false,
                    'is_test' => false,
                ],
                'secret_key' => [
                    'name' => 'STRIPE_SECRET_KEY',
                    'description' => 'Stripe live environment secret key (starts with sk_live_)',
                    'is_secret' => true,
                    'is_test' => false,
                ],
                'webhook_secret' => [
                    'name' => 'STRIPE_WEBHOOK_SECRET',
                    'description' => 'Stripe live environment webhook signing secret (starts with whsec_)',
                    'is_secret' => true,
                    'is_test' => false,
                ],
                'webhook_endpoint_id' => [
                    'name' => 'STRIPE_WEBHOOK_ENDPOINT_ID',
                    'description' => 'Stripe live environment webhook endpoint ID (starts with we_)',
                    'is_secret' => false,
                    'is_test' => false,
                ],
            ],
            self::MAILCHIMP => [
                'api_key' => [
                    'name' => 'MAILCHIMP_API_KEY',
                    'description' => 'Your Mailchimp API key',
                    'is_secret' => true,
                    'is_test' => false,
                ],
                'list_id' => [
                    'name' => 'MAILCHIMP_LIST_ID',
                    'description' => 'Your Mailchimp list ID',
                    'is_secret' => false,
                    'is_test' => false,
                ],
            ],
            self::PAYPAL => [
                'client_id' => [
                    'name' => 'PAYPAL_CLIENT_ID',
                    'description' => 'Your PayPal client ID',
                    'is_secret' => false,
                    'is_test' => false,
                ],
                'client_secret' => [
                    'name' => 'PAYPAL_CLIENT_SECRET',
                    'description' => 'Your PayPal client secret',
                    'is_secret' => true,
                    'is_test' => false,
                ],
            ],
            self::BREVO => [
                'api_key' => [
                    'name' => 'BREVO_API_KEY',
                    'description' => 'Your Brevo API key',
                    'is_secret' => true,
                    'is_test' => false,
                ],
            ],
            self::MAILERLITE => [
                'api_key' => [
                    'name' => 'MAILERLITE_API_KEY',
                    'description' => 'Your MailerLite API key',
                    'is_secret' => true,
                    'is_test' => false,
                ],
            ],
            self::SENDGRID => [
                'api_key' => [
                    'name' => 'SENDGRID_API_KEY',
                    'description' => 'Your SendGrid API key',
                    'is_secret' => true,
                    'is_test' => false,
                ],
            ],
            self::TWILIO => [
                'account_sid' => [
                    'name' => 'TWILIO_ACCOUNT_SID',
                    'description' => 'Your Twilio Account SID',
                    'is_secret' => false,
                    'is_test' => false,
                ],
                'auth_token' => [
                    'name' => 'TWILIO_AUTH_TOKEN',
                    'description' => 'Your Twilio Auth Token',
                    'is_secret' => true,
                    'is_test' => false,
                ],
            ],
        };
    }

    public function getDisplayName(): string
    {
        return match($this) {
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::MAILCHIMP => 'Mailchimp',
            self::BREVO => 'Brevo',
            self::MAILERLITE => 'MailerLite',
            self::SENDGRID => 'SendGrid',
            self::TWILIO => 'Twilio',
        };
    }

    public function getTestVariables(): array
    {
        return array_filter($this->getVariables(), fn($config) => $config['is_test'] ?? false);
    }

    public function getLiveVariables(): array
    {
        return array_filter($this->getVariables(), fn($config) => !($config['is_test'] ?? false));
    }

    public function hasTestVariables(): bool
    {
        return count($this->getTestVariables()) > 0;
    }

    public function getVariablesForMode(bool $isTestMode): array
    {
        return $isTestMode ? $this->getTestVariables() : $this->getLiveVariables();
    }
} 