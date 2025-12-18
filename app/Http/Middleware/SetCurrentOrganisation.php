<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Session\Session;

class SetCurrentOrganisation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        // If no organization is set in the session, get the user's personal organization
        if (! $request->session()->has('current_organisation_id')) {
            $personalOrganisation = $request->user()->organisations()
                ->where('personal_organisation', true)
                ->first();

            if ($personalOrganisation) {
                $request->session()->put('current_organisation_id', $personalOrganisation->id);
            }
        }

        // Share the current organisation with all views
        if ($request->user()) {
            app()->instance('current_organisation', $request->user()->currentOrganisation());
        }

        return $next($request);
    }
} 