<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('view', $organisation);

        // Get customers who have made payments to this organisation
        $customers = Customer::whereHas('products', function ($query) use ($organisation) {
            $query->where('organisation_id', $organisation->id);
        })
        ->orWhereExists(function ($query) use ($organisation) {
            $query->select('id')
                  ->from('payments')
                  ->where('organisation_id', $organisation->id)
                  ->whereRaw('(metadata->>\'customer_id\')::int = customers.id');
        })
        ->with(['products' => function ($query) use ($organisation) {
            $query->where('organisation_id', $organisation->id);
        }])
        ->latest()
        ->paginate(10);

        return Inertia::render('customers/index', [
            'customers' => $customers,
        ]);
    }
} 