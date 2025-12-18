<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $organisation = auth()->user()->currentOrganisation();
        $this->authorize('view', $organisation);

        $payments = $organisation->payments()
            ->with(['product', 'stripePayment'])
            ->latest()
            ->paginate(10);

        return Inertia::render('payments/index', [
            'payments' => $payments,
        ]);
    }
} 