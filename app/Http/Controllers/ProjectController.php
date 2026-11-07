<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Project::class, $business]);

        $projects = Project::where('business_id', $business->id)->with('customer')->latest()->paginate(20);

        if (view()->exists('business.projects.index')) {
            return view('business.projects.index', compact('business', 'projects'));
        }

        return response()->json(['projects' => $projects]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Project::class, $business]);
        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->get();
        if (view()->exists('business.projects.create')) {
            return view('business.projects.create', compact('business', 'customers'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Project::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'project_code' => ['required', 'string', 'max:50', 'unique:projects,project_code'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['status'] = $request->status ?? 'planning';
        $validated['priority'] = $request->priority ?? 'medium';

        $project = Project::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'project' => $project]);
        }

        return redirect()->route('business.projects.show', [$business, $project])->with('success', 'Project created successfully.');
    }

    public function show(Business $business, Project $project): View|JsonResponse
    {
        $this->authorize('view', [$project, $business]);
        $project->load('customer');
        if (view()->exists('business.projects.show')) {
            return view('business.projects.show', compact('business', 'project'));
        }
        return response()->json(['project' => $project]);
    }
}
