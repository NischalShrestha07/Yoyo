<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Sale;
use App\Services\Sales\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private readonly SalesService $sales
    ) {
    }

    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $query = Sale::query()
            ->where('business_id', $business->id)
            ->with(['customer', 'user', 'items'])
            ->when($request->search, function ($q, $search) {
                $q->where('invoice_number', 'like', "%{$search}%");
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->from, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->to, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest();

        $sales = $query->paginate(20)->withQueryString();

        if (view()->exists('business.sales.index')) {
            return view('business.sales.index', compact('business', 'sales'));
        }

        return response()->json(['sales' => $sales]);
    }

    public function show(Business $business, Sale $sale): View|JsonResponse
    {
        $this->authorize('view', [$sale, $business]);

        $sale->load(['items', 'payments', 'returns', 'customer', 'user']);

        if (view()->exists('business.sales.show')) {
            return view('business.sales.show', compact('business', 'sale'));
        }

        return response()->json(['sale' => $sale]);
    }

    public function summary(Request $request, Business $business): JsonResponse|View
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $summary = $this->sales->salesSummary($business, $request->only(['from', 'to']));

        if ($request->expectsJson()) {
            return response()->json($summary);
        }

        if (view()->exists('business.sales.summary')) {
            return view('business.sales.summary', compact('business', 'summary'));
        }

        return response()->json($summary);
    }

    public function return(Request $request, Business $business, Sale $sale): RedirectResponse|JsonResponse
    {
        $this->authorize('refund', [Sale::class, $business]);

        $request->validate([
            'items' => ['required', 'array'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $saleReturn = $this->sales->returnSale($business, auth()->user(), $sale, $request->all());
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'return' => $saleReturn]);
            }
            return redirect()->route('business.sales.show', [$business, $sale])->with('success', 'Return processed successfully.');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, Business $business, Sale $sale): RedirectResponse|JsonResponse
    {
        $this->authorize('cancel', [$sale, $business]);

        try {
            $this->sales->cancelSale($business, auth()->user(), $sale, $request->input('reason'));
            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('business.sales.show', [$business, $sale])->with('success', 'Sale cancelled successfully.');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
