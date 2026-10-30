<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\Membership\InviteUserRequest;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Business $business): View
    {
        $this->authorize('viewAny', $business);

        $memberships = $business->memberships()
            ->with(['user', 'roles'])
            ->latest()
            ->paginate(10);

        return view('platform.memberships.index', compact('business', 'memberships'));
    }

    public function create(Business $business): View
    {
        $this->authorize('create', $business);

        return view('platform.memberships.create', compact('business'));
    }

    public function store(InviteUserRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('create', $business);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Update existing membership
            $membership = $business->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'invited',
                    'invited_at' => now(),
                    'is_default' => false,
                ]
            );

            if ($membership->status === 'active') {
                return redirect()->route('platform.memberships.index', $business)
                    ->with('error', 'User is already a member of this business.');
            }
        } else {
            // Create user first, then membership
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt('password'), // Temporary password, user should reset
                'is_active' => true,
                'activated_at' => now(),
            ]);

            $business->memberships()->create([
                'user_id' => $user->id,
                'status' => 'invited',
                'invited_at' => now(),
                'is_default' => false,
            ]);
        }

        // TODO: Send invitation email

        return redirect()->route('platform.memberships.index', $business)
            ->with('success', 'User invited successfully.');
    }

    public function edit(Business $business, BusinessMembership $membership): View
    {
        $this->authorize('update', $business, $membership);

        $membership->load(['user', 'roles']);

        return view('platform.memberships.edit', compact('business', 'membership'));
    }

    public function update(Request $request, Business $business, BusinessMembership $membership): RedirectResponse
    {
        $this->authorize('update', $business, $membership);

        $request->validate([
            'status' => ['required', 'in:invited,active,suspended,inactive'],
            'roles' => ['array', 'exists:roles,id'],
        ]);

        $membership->update([
            'status' => $request->status,
            'joined_at' => $request->status === 'active' ? now() : $membership->joined_at,
            'revoked_at' => $request->status === 'inactive' ? now() : null,
        ]);

        // Update roles
        if ($request->has('roles')) {
            $membership->roles()->sync($request->roles);
        }

        return redirect()->route('platform.memberships.index', $business)
            ->with('success', 'Membership updated successfully.');
    }

    public function destroy(Business $business, BusinessMembership $membership): RedirectResponse
    {
        $this->authorize('delete', $business, $membership);

        $membership->update([
            'status' => 'inactive',
            'revoked_at' => now(),
            'revoked_by' => auth()->id(),
        ]);

        return redirect()->route('platform.memberships.index', $business)
            ->with('success', 'Membership revoked successfully.');
    }
}