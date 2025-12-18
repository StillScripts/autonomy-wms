<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get products filtered by product type for a specific organisation
     */
    public function getProductsByType(Request $request, Organisation $organisation)
    {
        $productTypeSlug = $request->query('type');
        
        $query = $organisation->products()
            ->with(['stripeProduct', 'productTypes', 'privateFiles'])
            ->when($productTypeSlug, function ($query) use ($productTypeSlug) {
                $query->whereHas('productTypes', function ($query) use ($productTypeSlug) {
                    $query->where('slug', $productTypeSlug);
                });
            })
            ->whereHas('stripeProduct', function ($query) {
                $query->where('stripe_environment', '!=', 'test');
            });

        $products = $query->get();

        // Add signed URLs for private files
        $products->each(function ($product) {
            if ($product->private_files) {
                $product->private_files->each(function ($file) {
                    $file->signed_url = $file->getTemporaryUrl(60); // 60 minutes expiry
                });
            }
        });

        return response()->json($products);
    }

    public function checkAccess(Request $request, Product $product)
    {
        $customer = $request->user();
        $hasAccess = $customer && $customer->products()->where('products.id', $product->id)->exists();
        return response()->json([
            'has_access' => $hasAccess,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
    }
} 