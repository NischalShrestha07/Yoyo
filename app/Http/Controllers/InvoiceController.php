<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $invoices = Sale::query()
            ->where('business_id', $business->id)
            ->whereNotNull('invoice_number')
            ->with(['customer'])
            ->latest()
            ->paginate(25);

        if (view()->exists('business.invoices.index')) {
            return view('business.invoices.index', compact('business', 'invoices'));
        }

        return response()->json(['invoices' => $invoices]);
    }

    public function show(Business $business, Sale $sale): View|JsonResponse
    {
        $this->authorize('view', [$sale, $business]);
        $sale->load(['items.product', 'customer', 'business', 'payments']);

        if (view()->exists('business.invoices.show')) {
            return view('business.invoices.show', compact('business', 'sale'));
        }

        return response()->json(['invoice' => $sale]);
    }

    public function print(Business $business, Sale $sale): View
    {
        $this->authorize('view', [$sale, $business]);
        $sale->load(['items.product', 'customer', 'business', 'payments']);

        if (view()->exists('business.invoices.print')) {
            return view('business.invoices.print', compact('business', 'sale'));
        }

        return view('business.invoices.show', compact('business', 'sale'));
    }
}
