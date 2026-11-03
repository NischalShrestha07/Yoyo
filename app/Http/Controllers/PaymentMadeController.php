<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Business;
use App\Models\PaymentMade;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentMadeController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [PaymentMade::class, $business]);

        $payments = PaymentMade::where('business_id', $business->id)->with(['vendor', 'bill'])->latest()->paginate(20);

        if (view()->exists('business.payments-made.index')) {
            return view('business.payments-made.index', compact('business', 'payments'));
        }

        return response()->json(['payments' => $payments]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [PaymentMade::class, $business]);
        $vendors = Vendor::where('business_id', $business->id)->where('is_active', true)->get();
        $bills = Bill::where('business_id', $business->id)->whereIn('status', ['unpaid', 'partial'])->get();
        if (view()->exists('business.payments-made.create')) {
            return view('business.payments-made.create', compact('business', 'vendors', 'bills'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [PaymentMade::class, $business]);

        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bill_id' => ['nullable', 'exists:bills,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string'],
        ]);

        $payment = DB::transaction(function () use ($validated, $business, $request) {
            $payment = PaymentMade::create([
                'business_id' => $business->id,
                'vendor_id' => $validated['vendor_id'],
                'bill_id' => $validated['bill_id'] ?? null,
                'user_id' => auth()->id(),
                'payment_number' => 'PM-'.strtoupper(uniqid()),
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            if ($validated['bill_id']) {
                $bill = Bill::find($validated['bill_id']);
                if ($bill) {
                    $bill->amount_paid += $validated['amount'];
                    $bill->amount_due = max(0, $bill->total_amount - $bill->amount_paid);
                    $bill->status = $bill->amount_due <= 0 ? 'paid' : 'partial';
                    $bill->save();
                }
            }

            return $payment;
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'payment' => $payment]);
        }

        return redirect()->route('business.payments-made.index', $business)->with('success', 'Payment recorded successfully.');
    }
}
