<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdeasApiService
{
    private PendingRequest $http;
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ideas_api.base_url', 'http://localhost:3000');
        $this->apiKey = config('services.ideas_api.api_key');

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        $this->http = Http::withHeaders($headers)->timeout(30);
    }

    /**
     * Generate a landing page idea based on conversation messages.
     *
     * @param array<array{role: string, content: string}> $messages
     * @return array{title: string, summary: string, sections: array, message: string}
     * @throws \Exception
     */
    public function generateLandingPageIdea(array $messages): array
    {
        if (!$this->apiKey) {
            Log::warning('Ideas API key not configured', [
                'hint' => 'Set IDEAS_API_KEY environment variable to enable AI-powered page generation.',
            ]);
            throw new \Exception('Ideas API is not configured. Please set the API key in your environment variables.');
        }

        try {
            $response = $this->http->post("{$this->baseUrl}/ideas/landing-page", [
                'messages' => $messages,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            $this->handleErrorResponse($response);

        } catch (RequestException $e) {
            Log::error('Ideas API request failed', [
                'error' => $e->getMessage(),
                'messages' => $messages,
            ]);

            throw new \Exception('Failed to communicate with Ideas API: ' . $e->getMessage());
        }
    }

    /**
     * Handle error responses from the Ideas API.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @throws \Exception
     */
    private function handleErrorResponse($response): void
    {
        $statusCode = $response->status();
        $errorData = $response->json();

        $errorMessage = match ($statusCode) {
            401 => 'Unauthorized: Invalid API key',
            400 => 'Bad Request: Invalid input data',
            422 => 'Validation Error: ' . ($errorData['error'] ?? 'Invalid response format'),
            500 => 'Internal Server Error: ' . ($errorData['error'] ?? 'Unknown error'),
            default => "HTTP {$statusCode}: " . ($errorData['error'] ?? 'Unknown error'),
        };

        Log::error('Ideas API error', [
            'status_code' => $statusCode,
            'error_data' => $errorData,
        ]);

        throw new \Exception($errorMessage);
    }

    /**
     * Test the connection to the Ideas API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->http->get("{$this->baseUrl}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Ideas API connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
} 