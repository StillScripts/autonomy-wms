<?php

namespace App\Console\Commands;

use App\Services\IdeasApiService;
use Illuminate\Console\Command;

class TestIdeasApiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ideas-api:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the connection to the Ideas API';

    /**
     * Execute the console command.
     */
    public function handle(IdeasApiService $ideasApiService): int
    {
        $this->info('Testing connection to Ideas API...');

        try {
            $isConnected = $ideasApiService->testConnection();

            if ($isConnected) {
                $this->info('✅ Successfully connected to Ideas API');
                return self::SUCCESS;
            } else {
                $this->error('❌ Failed to connect to Ideas API');
                $this->line('Please check your configuration:');
                $this->line('- IDEAS_API_BASE_URL: ' . config('services.ideas_api.base_url'));
                $this->line('- IDEAS_API_KEY: ' . (config('services.ideas_api.api_key') ? 'Set' : 'Not set'));
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error testing connection: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
} 