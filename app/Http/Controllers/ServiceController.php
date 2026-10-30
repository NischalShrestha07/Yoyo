<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [Service::class, $business]);

        $query = Service::where('business_id', $business->id)
            ->with(['category'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            });

        $services = $query->latest()->paginate(15);
        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.services.index', compact('business', 'services', 'categories'));
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Service::class, $business]);

        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.services.create', compact('business', 'categories'));
    }

    public function store(StoreServiceRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [Service::class, $business]);

        $service = Service::create([
            'business_id' => $business->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'code' => $request->code,
            'description' => $request->description,
            'selling_price' => $request->selling_price,
            'is_active' => $request->boolean('is_active', true),
            'attributes' => $request->attributes ?? [],
        ]);

        return redirect()->route('business.services.show', [$business, $service])
            ->with('success', 'Service created successfully.');
    }

    public function show(Business $business, Service $service)
    {
        $this->authorize('view', [$business, $service]);

        $service->load(['category']);

        if (view()->exists('business.services.show')) {
            return view('business.services.show', compact('business', 'service'));
        }

        return response()->json(['service' => $service]);
    }

    public function edit(Business $business, Service $service): View
    {
        $this->authorize('update', [$business, $service]);

        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.services.edit', compact('business', 'service', 'categories'));
    }

    public function update(UpdateServiceRequest $request, Business $business, Service $service): RedirectResponse
    {
        $this->authorize('update', [$business, $service]);

        $service->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'code' => $request->code,
            'description' => $request->description,
            'selling_price' => $request->selling_price,
            'is_active' => $request->boolean('is_active', true),
            'attributes' => $request->attributes ?? [],
        ]);

        return redirect()->route('business.services.show', [$business, $service])
            ->with('success', 'Service updated successfully.');
    }

    public function archive(Business $business, Service $service): RedirectResponse
    {
        $this->authorize('archive', [$business, $service]);

        $service->update(['is_active' => false]);

        return redirect()->route('business.services.index', $business)
            ->with('success', 'Service archived successfully.');
    }

    public function destroy(Business $business, Service $service): RedirectResponse
    {
        $this->authorize('delete', [$business, $service]);

        $service->delete();

        return redirect()->route('business.services.index', $business)
            ->with('success', 'Service deleted successfully.');
    }
}
