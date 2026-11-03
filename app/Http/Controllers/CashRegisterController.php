<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\CashRegister;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [CashRegister::class, $business]);

        $registers = CashRegister::query()
            ->where('business_id', $business->id)
            ->with(['branch', 'user'])
            ->latest()
            ->paginate(20);

        if (view()->exists('business.cash-registers.index')) {
            return view('business.cash-registers.index', compact('business', 'registers'));
        }

        return response()->json(['registers' => $registers]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [CashRegister::class, $business]);
        if (view()->exists('business.cash-registers.create')) {
            return view('business.cash-registers.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [CashRegister::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['user_id'] = auth()->id();
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['status'] = 'open';
        $validated['opened_at'] = now();

        $register = CashRegister::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'register' => $register]);
        }

        return redirect()->route('business.cash-registers.index', $business)->with('success', 'Cash register opened.');
    }

    public function close(Request $request, Business $business, CashRegister $cashRegister): RedirectResponse|JsonResponse
    {
        $this->authorize('update', [$cashRegister, $business]);

        $cashRegister->update([
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('business.cash-registers.index', $business)->with('success', 'Cash register closed.');
    }
}
