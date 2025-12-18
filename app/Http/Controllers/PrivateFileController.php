<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrivateFileRequest;
use App\Models\PrivateFile;
use App\Services\PrivateFileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class PrivateFileController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PrivateFileService $privateFileService
    ) {}

    /**
     * Display a listing of the private files.
     */
    public function index(Request $request): Response
    {
        $currentOrganisation = auth()->user()->currentOrganisation();

        if (!$currentOrganisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        $privateFiles = $currentOrganisation->privateFiles()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('private-files/index', [
            'privateFiles' => $privateFiles->through(fn ($file) => [
                'id' => $file->id,
                'name' => $file->name,
                'description' => $file->description,
                'content_type' => $file->content_type,
                'file_size' => $file->formatted_file_size,
                'created_at' => $file->created_at->format('Y-m-d H:i:s'),
                'active' => $file->active,
            ]),
            'currentOrganisation' => $currentOrganisation
        ]);
    }

    /**
     * Show the form for creating a new private file.
     */
    public function create(): Response
    {
        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        return Inertia::render('private-files/create', [
            'contentTypes' => PrivateFile::CONTENT_TYPES,
            'organisation' => $organisation,
        ]);
    }

    /**
     * Store a newly created private file in storage.
     */
    public function store(PrivateFileRequest $request)
    {
        Log::info('[PrivateFileController] Store method called', [
            'request_method' => $request->method(),
            'content_type_header' => $request->header('Content-Type'),
            'has_file' => $request->hasFile('file'),
        ]);

        $organisation = auth()->user()->currentOrganisation();
        
        if (!$organisation) {
            Log::error('[PrivateFileController] No organisation found for user', ['user_id' => auth()->id()]);
            return to_route('dashboard')->with('error', 'No organisation found. Please create one first.');
        }

        // Log file details if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Log::info('[PrivateFileController] File details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'size_mb' => round($file->getSize() / (1024 * 1024), 2),
                'extension' => $file->getClientOriginalExtension(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage(),
            ]);

            // Check if it's an MP3 file
            if ($file->getMimeType() === 'audio/mpeg' || strtolower($file->getClientOriginalExtension()) === 'mp3') {
                Log::info('[PrivateFileController] MP3 file detected');
            }
        } else {
            Log::error('[PrivateFileController] No file uploaded in request');
        }

        // Log validated data
        $data = $request->validated();
        Log::info('[PrivateFileController] Validated data', [
            'name' => $data['name'] ?? null,
            'content_type' => $data['content_type'] ?? null,
            'has_description' => isset($data['description']),
            'active' => $data['active'] ?? null,
        ]);

        $data['organisation_id'] = $organisation->id;

        try {
            Log::info('[PrivateFileController] Attempting to upload file via PrivateFileService');
            
            $privateFile = $this->privateFileService->upload(
                $request->file('file'),
                $data
            );

            Log::info('[PrivateFileController] File uploaded successfully', [
                'private_file_id' => $privateFile->id,
                'file_path' => $privateFile->file_path,
                'file_size' => $privateFile->file_size,
            ]);

            return redirect()->route('private-files.show', $privateFile)
                ->with('success', 'Private file uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('[PrivateFileController] File upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'Failed to upload file: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified private file.
     */
    public function show(PrivateFile $privateFile): Response
    {
        $this->authorize('view', $privateFile);

        // Generate a temporary URL valid for 60 minutes
        $temporaryUrl = $privateFile->getTemporaryUrl(60);

        return Inertia::render('private-files/show', [
            'privateFile' => [
                'id' => $privateFile->id,
                'name' => $privateFile->name,
                'description' => $privateFile->description,
                'content_type' => $privateFile->content_type,
                'file_name' => $privateFile->file_name,
                'mime_type' => $privateFile->mime_type,
                'file_size' => $privateFile->formatted_file_size,
                'created_at' => $privateFile->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $privateFile->updated_at->format('Y-m-d H:i:s'),
                'active' => $privateFile->active,
                'temporary_url' => $temporaryUrl,
                'metadata' => $privateFile->metadata,
            ],
        ]);
    }
} 