<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Business;
use App\Models\DebitNote;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebitNoteController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [DebitNote::class, $business]);

        $debitNotes = DebitNote::where('business_id', $business->id)->with(['vendor'])->latest()->paginate(20);

        if (view()->exists('business.debit-notes.index')) {
            return view('business.debit-notes.index', compact('business', 'debitNotes'));
        }

        return response()->json(['debit_notes' => $debitNotes]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [DebitNote::class, $business]);
        $vendors = Vendor::where('business_id', $business->id)->where('is_active', true)->get();
        $bills = Bill::where('business_id', $business->id)->get();
        if (view()->exists('business.debit-notes.create')) {
            return view('business.debit-notes.create', compact('business', 'vendors', 'bills'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [DebitNote::class, $business]);

        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bill_id' => ['nullable', 'exists:bills,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $debitNote = DebitNote::create([
            'business_id' => $business->id,
            'vendor_id' => $validated['vendor_id'],
            'bill_id' => $validated['bill_id'] ?? null,
            'user_id' => auth()->id(),
            'debit_note_number' => 'DN-'.strtoupper(uniqid()),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'approved',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'debit_note' => $debitNote]);
        }

        return redirect()->route('business.debit-notes.index', $business)->with('success', 'Debit note created successfully.');
    }

    public function show(Business $business, DebitNote $debitNote): View|JsonResponse
    {
        $this->authorize('view', [$debitNote, $business]);
        $debitNote->load(['vendor', 'bill']);
        if (view()->exists('business.debit-notes.show')) {
            return view('business.debit-notes.show', compact('business', 'debitNote'));
        }
        return response()->json(['debit_note' => $debitNote]);
    }
}
