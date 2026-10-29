<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\Business\CreateBusinessRequest;
use App\Http\Requests\Platform\Business\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Business::class);

        $query = Business::query()
            ->with(['creator', 'memberships.user'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        $businesses = $query->latest()->paginate(10);

        return view('platform.businesses.index', compact('businesses'));
    }

    public function create(): View
    {
        $this->authorize('create', Business::class);

        return view('platform.businesses.create');
    }

    public function store(CreateBusinessRequest $request): RedirectResponse
    {
        $this->authorize('create', Business::class);

        $business = Business::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'type' => $request->type,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'tax_id' => $request->tax_id,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Create membership for the creator
        $business->memberships()->create([
            'user_id' => auth()->id(),
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        return redirect()->route('platform.businesses.index')
            ->with('success', 'Business created successfully.');
    }

    public function edit(Business $business): View
    {
        $this->authorize('update', $business);

        return view('platform.businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

        $business->update([
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'tax_id' => $request->tax_id,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('platform.businesses.index')
            ->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $this->authorize('delete', $business);

        $business->delete();

        return redirect()->route('platform.businesses.index')
            ->with('success', 'Business deleted successfully.');
    }

    public function show(Business $business): View
    {
        $this->authorize('view', $business);

        $business->load(['creator', 'memberships.user', 'modules', 'roles']);

        return view('platform.businesses.show', compact('business'));
    }
}