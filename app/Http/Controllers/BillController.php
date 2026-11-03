<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Business;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Bill::class, $business]);

        $bills = Bill::where('business_id', $business->id)->with('vendor')->latest()->paginate(20);

        if (view()->exists('business.bills.index')) {
            return view('business.bills.index', compact('business', 'bills'));
        }

        return response()->json(['bills' => $bills]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Bill::class, $business]);
        $vendors = Vendor::where('business_id', $business->id)->where('is_active', true)->get();
        if (view()->exists('business.bills.create')) {
            return view('business.bills.create', compact('business', 'vendors'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Bill::class, $business]);

        $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bill_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $bill = DB::transaction(function () use ($request, $business) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $bill = Bill::create([
                'business_id' => $business->id,
                'vendor_id' => $request->vendor_id,
                'user_id' => auth()->id(),
                'bill_number' => 'BILL-'.strtoupper(uniqid()),
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
                'amount_paid' => 0,
                'amount_due' => $subtotal,
                'status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $bill;
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'bill' => $bill]);
        }

        return redirect()->route('business.bills.show', [$business, $bill])->with('success', 'Bill created successfully.');
    }

    public function show(Business $business, Bill $bill): View|JsonResponse
    {
        $this->authorize('view', [$bill, $business]);
        $bill->load(['vendor', 'items', 'user']);
        if (view()->exists('business.bills.show')) {
            return view('business.bills.show', compact('business', 'bill'));
        }
        return response()->json(['bill' => $bill]);
    }
}
