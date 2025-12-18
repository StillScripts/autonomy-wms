<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\ThirdPartyProvider;
use App\Models\Organisation;
use App\Http\Resources\ThirdPartyProviderableResource;
use Illuminate\Support\Facades\Log;

class ThirdPartyProviderableController extends Controller
{
    public function index() 
    {
        $organisation = auth()->user()->currentOrganisation();
        $enabledProviders = $organisation->getEnabledThirdPartyProviders();
        
        // Group variable values by provider for display
        $providerConfigurations = collect($enabledProviders)->map(function($provider) use ($organisation) {
            $variables = $organisation->thirdPartyVariableValues()
                ->where('provider', $provider->value)
                ->get();
                
            return [
                'provider' => [
                    'value' => $provider->value,
                    'display_name' => $provider->getDisplayName(),
                    'variables' => $provider->getVariables(),
                ],
                'provider_name' => $provider->getDisplayName(),
                'variables' => $variables,
                'variable_count' => $variables->count(),
            ];
        });

        return Inertia::render('third-parties/index', [
            'providerConfigurations' => $providerConfigurations,
            'availableProviders' => collect(ThirdPartyProvider::cases())->map(fn($provider) => [
                'value' => $provider->value,
                'display_name' => $provider->getDisplayName(),
                'variables' => $provider->getVariables(),
            ]),
        ]);
    }

    public function create(Request $request)
    {
        $availableProviders = collect(ThirdPartyProvider::cases())->map(fn($provider) => [
            'value' => $provider->value,
            'display_name' => $provider->getDisplayName(),
            'variables' => $provider->getVariables(),
        ]);

        return Inertia::render('third-parties/create', [
            'availableProviders' => $availableProviders,
        ]);
    }

    public function store(Request $request)
    {
        $organisation = auth()->user()->currentOrganisation();

        $request->validate([
            'provider' => 'required|string|in:' . implode(',', array_column(ThirdPartyProvider::cases(), 'value')),
        ]);

        $provider = ThirdPartyProvider::from($request->provider);

        return redirect()->route('third-parties.edit', ['provider' => $provider->value]);
    }

    public function edit(Request $request)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        $providerValue = $request->query('provider');
        if (!$providerValue) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Provider parameter is required');
        }
        
        try {
            $provider = ThirdPartyProvider::from($providerValue);
        } catch (\ValueError $e) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Invalid provider specified');
        }
        
        $variables = $organisation->thirdPartyVariableValues()
            ->where('provider', $provider->value)
            ->get()
            ->keyBy('variable_key');
            
        $availableVariables = $provider->getVariables();
        
        // Create current values array
        $currentValues = [];
        foreach ($availableVariables as $key => $config) {
            $currentValues[$key] = $variables->get($key)?->value ?? '';
        }

        return Inertia::render('third-parties/edit', [
            'provider' => [
                'value' => $provider->value,
                'display_name' => $provider->getDisplayName(),
                'variables' => $availableVariables,
            ],
            'currentValues' => $currentValues,
        ]);
    }

    public function update(Request $request)
    {
        $organisation = auth()->user()->currentOrganisation();

        $providerValue = $request->query('provider');
        if (!$providerValue) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Provider parameter is required');
        }

        try {
            $provider = ThirdPartyProvider::from($providerValue);
        } catch (\ValueError $e) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Invalid provider specified');
        }

        $request->validate([
            'variables' => 'required|array',
        ]);
        $availableVariables = $provider->getVariables();

        // Update the variables
        foreach ($request->variables as $key => $value) {
            if (!array_key_exists($key, $availableVariables)) {
                continue; // Skip invalid variables
            }
            
            if (!empty($value)) {
                $organisation->setThirdPartyVariableValue($provider, $key, $value);
            } else {
                // Remove the variable if empty
                $organisation->thirdPartyVariableValues()
                    ->where('provider', $provider->value)
                    ->where('variable_key', $key)
                    ->delete();
            }
        }

        // Handle Stripe webhook configuration
        if ($provider === ThirdPartyProvider::STRIPE) {
            try {
                $stripeService = new \App\Services\StripeService($organisation);
                
                // Check if test keys were set
                $testPublicKey = $request->variables['test_public_key'] ?? null;
                $testSecretKey = $request->variables['test_secret_key'] ?? null;
                if ($testPublicKey && $testSecretKey) {
                    $stripeService->configureWebhook('test');
                }
                
                // Check if live keys were set
                $livePublicKey = $request->variables['public_key'] ?? null;
                $liveSecretKey = $request->variables['secret_key'] ?? null;
                if ($livePublicKey && $liveSecretKey) {
                    $stripeService->configureWebhook('live');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to configure Stripe webhook: ' . $e->getMessage());
                return redirect()->route('third-parties.index')
                    ->with('error', 'Stripe configuration updated but webhook setup failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('third-parties.index')
            ->with('success', "{$provider->getDisplayName()} configuration updated successfully");
    }

    public function destroy(Request $request)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        $providerValue = $request->query('provider');
        if (!$providerValue) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Provider parameter is required');
        }
        
        try {
            $provider = ThirdPartyProvider::from($providerValue);
        } catch (\ValueError $e) {
            return redirect()->route('third-parties.index')
                ->with('error', 'Invalid provider specified');
        }

        // Remove all variables for this provider
        $organisation->thirdPartyVariableValues()
            ->where('provider', $provider->value)
            ->delete();

        return redirect()->route('third-parties.index')
            ->with('success', "{$provider->getDisplayName()} configuration removed successfully");
    }
}
