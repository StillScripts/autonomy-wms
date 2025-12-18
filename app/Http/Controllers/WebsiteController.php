<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Services\FileUploadService;
use App\Http\Requests\Websites\WebsiteRequest;

class WebsiteController extends Controller
{
    use AuthorizesRequests;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Show the form for creating a new website.
     */
    public function create(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        return Inertia::render('websites/create', [
            'organisation' => $organisation,
        ]);
    }

    /**
     * Store a newly created website in storage.
     */
    public function store(WebsiteRequest $request)
    {
        $organisation = auth()->user()->currentOrganisation();
        
        Log::info('Store website attempt', [
            'user' => auth()->user()->id,
            'organisation' => $organisation ? $organisation->id : null,
            'request_data' => $request->all()
        ]);

        if (!$organisation) {
            Log::warning('Website creation failed - no organisation found');
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        try {
            $validated = $request->validated();

            if ($request->hasFile('logo')) {
                $path = $this->fileUploadService->upload(
                    $request->file('logo'),
                    'website-logos/' . $organisation->id
                );
                $validated['logo'] = $path;
            }

            $website = $organisation->websites()->create($validated);

            return to_route('websites.index', $website);

        } catch (\Exception $e) {
            Log::error('Website creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to create website: ' . $e->getMessage()]);
        }
    }


    public function index()
    {
        $currentOrganisation = auth()->user()->currentOrganisation();

        if (!$currentOrganisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        } 

        $websites = $currentOrganisation->websites->map(function ($website) {
            return $website;
        });

        return Inertia::render('websites/index', [
            'websites' => $websites,
            'currentOrganisation' => $currentOrganisation
        ]);
    }

    public function show(Website $website): Response
    {
        $this->authorize('view', $website);
        
        $user = auth()->user();
        $websiteOrganisation = $website->organisation;
        
        // Check if user belongs to the website's organisation and switch if different
        if ($websiteOrganisation->id !== $user->currentOrganisation()?->id) {
            try {
                $user->switchOrganisation($websiteOrganisation);
            } catch (\InvalidArgumentException $e) {
                // The switchOrganisation method already validates membership,
                // but we'll handle the error gracefully just in case
                Log::warning('Failed to switch organisation', [
                    'user_id' => $user->id,
                    'website_id' => $website->id,
                    'organisation_id' => $websiteOrganisation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Inertia::render('websites/show', [
            'website' => $website->load('organisation'),
            'pages' => $website->pages,
            'globalContentBlocks' => $website->globalContentBlocks()->with(['contentBlock.blockType'])->get(),
            'currentOrganisation' => $user->currentOrganisation()
        ]);
    }

    public function edit(Website $website): Response
    {
        $this->authorize('update', $website);

        return Inertia::render('websites/edit', [
            'website' => $website->load('organisation'),
        ]);
    }

    public function update(WebsiteRequest $request, Website $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $path = $this->fileUploadService->upload(
                $request->file('logo'),
                'website-logos/' . $website->organisation_id
            );
            $validated['logo'] = $path;
        }
        elseif (isset($validated['logo'])) {
            unset($validated['logo']);
        }

        try {
            $website->update($validated);
            return redirect()
                ->back()
                ->with('success', 'Website updated successfully');
        } catch (\Exception $e) {
            Log::error('Website update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to update website: ' . $e->getMessage()]);
        }
    }
}
