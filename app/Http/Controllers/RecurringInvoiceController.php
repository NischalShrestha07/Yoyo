<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\RecurringInvoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringInvoiceController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [RecurringInvoice::class, $business]);

        $recurring = RecurringInvoice::where('business_id', $business->id)->with('customer')->latest()->paginate(20);

        if (view()->exists('business.recurring-invoices.index')) {
            return view('business.recurring-invoices.index', compact('business', 'recurring'));
        }

        return response()->json(['recurring_invoices' => $recurring]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [RecurringInvoice::class, $business]);
        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->get();
        if (view()->exists('business.recurring-invoices.create')) {
            return view('business.recurring-invoices.create', compact('business', 'customers'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [RecurringInvoice::class, $business]);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
            'start_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $start = Carbon::parse($validated['start_date']);
        $next = match($validated['frequency']) {
            'daily' => $start->copy()->addDay(),
            'weekly' => $start->copy()->addWeek(),
            'monthly' => $start->copy()->addMonth(),
            'yearly' => $start->copy()->addYear(),
            default => $start->copy()->addMonth(),
        };

        $recurring = RecurringInvoice::create([
            'business_id' => $business->id,
            'customer_id' => $validated['customer_id'],
            'user_id' => auth()->id(),
            'recurring_number' => 'REC-'.strtoupper(uniqid()),
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'next_date' => $next,
            'subtotal' => $validated['total_amount'],
            'total_amount' => $validated['total_amount'],
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'recurring' => $recurring]);
        }

        return redirect()->route('business.recurring-invoices.index', $business)->with('success', 'Recurring invoice created successfully.');
    }
}
