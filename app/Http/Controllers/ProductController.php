<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Product;
use App\Models\ProductType;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('view', $organisation);

        $products = $organisation->products()
            ->with(['stripeProduct', 'productTypes'])
            ->latest()
            ->paginate(10);

        return Inertia::render('products/index', [
            'products' => $products,
        ]);
    }

    public function edit(Product $product)
    {
        // $this->authorize('update', $product);

        $availablePrivateFiles = $product->organisation->privateFiles()
            ->select(['id', 'name', 'description', 'content_type', 'file_size'])
            ->get();

        return Inertia::render('products/edit', [
            'product' => $product->load('privateFiles'),
            'availablePrivateFiles' => $availablePrivateFiles,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // $this->authorize('update', $product);

        $validated = $request->validate([
            'private_files' => 'array',
            'private_files.*.id' => 'required|exists:private_files,id',
            'private_files.*.sort_order' => 'required|integer|min:0',
            'product_types' => 'array',
            'product_types.*' => 'string|max:255',
        ]);

        // Sync the private files with their sort order
        $syncData = collect($validated['private_files'])->mapWithKeys(function ($file) {
            return [$file['id'] => ['sort_order' => $file['sort_order']]];
        })->all();

        $product->privateFiles()->sync($syncData);

        if (isset($validated['product_types'])) {
            $productTypeIds = collect($validated['product_types'])->map(function ($typeName) {
                return ProductType::firstOrCreate(
                    ['name' => $typeName],
                    ['slug' => \Str::slug($typeName)]
                )->id;
            })->all();

            $product->productTypes()->sync($productTypeIds);
        }

        return redirect()->route('products.index')
            ->with('message', 'Product files updated successfully');
    }

    public function sync(Request $request)
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('update', $organisation);

        $environment = $request->input('environment', 'test');
        
        try {
            $testStripeService = new StripeService($organisation, 'test');
            $liveStripeService = new StripeService($organisation, 'live');
            $stats = $testStripeService->syncProducts();
            $stats2 = $liveStripeService->syncProducts();
            $mergedStats = array_merge($stats, $stats2);

            return redirect()->route('products.index')->with([
                'message' => 'Products synced successfully',
                'stats' => $mergedStats,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Get all products with their associated private files and signed URLs
     */
    public function getAllProducts()
    {
        $products = Product::with(['private_files' => function ($query) {
            $query->select(['private_files.id', 'name', 'description', 'content_type', 'file_size']);
        }])->get();

        $products->each(function ($product) {
            $product->private_files->each(function ($file) {
                $file->signed_url = $file->getTemporaryUrl(60); // 60 minutes expiry
            });
        });

        return response()->json($products);
    }

    /**
     * Get a single product with its associated private files and signed URLs
     */
    public function getProduct(Product $product)
    {
        $product->load(['private_files' => function ($query) {
            $query->select(['private_files.id', 'name', 'description', 'content_type', 'file_size']);
        }]);

        $product->private_files->each(function ($file) {
            $file->signed_url = $file->getTemporaryUrl(60); // 60 minutes expiry
        });

        return response()->json($product);
    }
}
