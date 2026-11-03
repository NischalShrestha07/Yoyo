<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [CreditNote::class, $business]);

        $creditNotes = CreditNote::where('business_id', $business->id)->with(['customer'])->latest()->paginate(20);

        if (view()->exists('business.credit-notes.index')) {
            return view('business.credit-notes.index', compact('business', 'creditNotes'));
        }

        return response()->json(['credit_notes' => $creditNotes]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [CreditNote::class, $business]);
        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->get();
        $sales = Sale::where('business_id', $business->id)->where('status', 'completed')->latest()->limit(50)->get();
        if (view()->exists('business.credit-notes.create')) {
            return view('business.credit-notes.create', compact('business', 'customers', 'sales'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [CreditNote::class, $business]);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_id' => ['nullable', 'exists:sales,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $creditNote = CreditNote::create([
            'business_id' => $business->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'sale_id' => $validated['sale_id'] ?? null,
            'user_id' => auth()->id(),
            'credit_note_number' => 'CN-'.strtoupper(uniqid()),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'approved',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'credit_note' => $creditNote]);
        }

        return redirect()->route('business.credit-notes.index', $business)->with('success', 'Credit note created successfully.');
    }

    public function show(Business $business, CreditNote $creditNote): View|JsonResponse
    {
        $this->authorize('view', [$creditNote, $business]);
        $creditNote->load(['customer', 'sale']);
        if (view()->exists('business.credit-notes.show')) {
            return view('business.credit-notes.show', compact('business', 'creditNote'));
        }
        return response()->json(['credit_note' => $creditNote]);
    }
}
