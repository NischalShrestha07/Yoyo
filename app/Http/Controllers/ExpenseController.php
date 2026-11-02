<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Expense::class, $business]);

        $expenses = Expense::query()
            ->where('business_id', $business->id)
            ->when($request->from, fn($q, $f) => $q->whereDate('expense_date', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('expense_date', '<=', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $total = Expense::where('business_id', $business->id)->sum('amount');

        if (view()->exists('business.expenses.index')) {
            return view('business.expenses.index', compact('business', 'expenses', 'total'));
        }

        return response()->json(['expenses' => $expenses, 'total' => $total]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Expense::class, $business]);
        if (view()->exists('business.expenses.create')) {
            return view('business.expenses.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Expense::class, $business]);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['user_id'] = auth()->id();

        $expense = Expense::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'expense' => $expense]);
        }

        return redirect()->route('business.expenses.index', $business)->with('success', 'Expense recorded successfully.');
    }

    public function show(Business $business, Expense $expense): View|JsonResponse
    {
        $this->authorize('view', [$expense, $business]);
        if (view()->exists('business.expenses.show')) {
            return view('business.expenses.show', compact('business', 'expense'));
        }
        return response()->json(['expense' => $expense]);
    }

    public function destroy(Business $business, Expense $expense): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', [$expense, $business]);
        $expense->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('business.expenses.index', $business)->with('success', 'Expense deleted successfully.');
    }
}
