<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Quotation::class, $business]);

        $quotations = Quotation::query()
            ->where('business_id', $business->id)
            ->with(['customer'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if (view()->exists('business.quotations.index')) {
            return view('business.quotations.index', compact('business', 'quotations'));
        }

        return response()->json(['quotations' => $quotations]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Quotation::class, $business]);
        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();
        if (view()->exists('business.quotations.create')) {
            return view('business.quotations.create', compact('business', 'customers', 'products'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Quotation::class, $business]);

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $quote = DB::transaction(function () use ($request, $business) {
            $subtotal = 0;
            $items = $request->input('items', []);
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            $discount = (float) ($request->discount_amount ?? 0);
            $taxRate = (float) ($business->getSetting('sales.tax_rate', 0) ?? 0) / 100.0;
            $taxable = max(0, $subtotal - $discount);
            $tax = round($taxable * $taxRate, 2);
            $total = round($taxable + $tax, 2);

            $quote = Quotation::create([
                'business_id' => $business->id,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'quote_number' => 'QT-'.strtoupper(uniqid()),
                'status' => 'draft',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'valid_until' => $request->valid_until,
                'notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                QuotationItem::create([
                    'business_id' => $business->id,
                    'quotation_id' => $quote->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
            return $quote;
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'quotation' => $quote]);
        }

        return redirect()->route('business.quotations.show', [$business, $quote])->with('success', 'Quotation created successfully.');
    }

    public function show(Business $business, Quotation $quotation): View|JsonResponse
    {
        $this->authorize('view', [$quotation, $business]);
        $quotation->load(['customer', 'user', 'items']);
        if (view()->exists('business.quotations.show')) {
            return view('business.quotations.show', compact('business', 'quotation'));
        }
        return response()->json(['quotation' => $quotation]);
    }

    public function convertToSale(Business $business, Quotation $quotation): RedirectResponse|JsonResponse
    {
        $this->authorize('update', [$quotation, $business]);
        $quotation->update(['status' => 'accepted']);
        if ($request ?? false && request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('business.pos.index', $business)->with('success', 'Quotation accepted. Create sale from POS.');
    }
}
