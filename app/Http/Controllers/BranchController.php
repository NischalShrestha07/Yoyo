<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Branch::class, $business]);

        $branches = Branch::where('business_id', $business->id)->latest()->paginate(20);

        if (view()->exists('business.branches.index')) {
            return view('business.branches.index', compact('business', 'branches'));
        }

        return response()->json(['branches' => $branches]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Branch::class, $business]);
        if (view()->exists('business.branches.create')) {
            return view('business.branches.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Branch::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_default'] = $request->boolean('is_default', false);

        if ($validated['is_default']) {
            Branch::where('business_id', $business->id)->update(['is_default' => false]);
        }

        $branch = Branch::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'branch' => $branch]);
        }

        return redirect()->route('business.branches.index', $business)->with('success', 'Branch created successfully.');
    }

    public function show(Business $business, Branch $branch): View|JsonResponse
    {
        $this->authorize('view', [$branch, $business]);
        if (view()->exists('business.branches.show')) {
            return view('business.branches.show', compact('business', 'branch'));
        }
        return response()->json(['branch' => $branch]);
    }
}
