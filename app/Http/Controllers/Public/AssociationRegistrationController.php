<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\AssociationRegistrationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\AssociationRegisteredNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Public self-registration entry point for associations (الجمعيات).
 *
 * Flow (Sprint 1):
 *   1. Visitor opens /register/association
 *   2. Submits the form (validated by AssociationRegistrationRequest)
 *   3. We create:
 *        - an Organization (type=association, status=pending)
 *        - a User (status=pending, role=association_manager,
 *                  primary_organization_id = new org id)
 *   4. We notify every super_admin (database + mail) and redirect the
 *      visitor to /register/association/pending with a session flag so
 *      the standalone pending page cannot be opened directly.
 *
 * Consultants and Donor users are NOT registered through this flow —
 * they are created from inside the super_admin panel.
 */
class AssociationRegistrationController extends Controller
{
    public function show(): View
    {
        return view('auth.register.association');
    }

    public function store(AssociationRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Organization $org */
        $org = DB::transaction(function () use ($data) {
            $organization = Organization::create([
                'type' => config('makeen.organization_types.association'),
                'name_ar' => $data['org_name_ar'],
                'name_en' => $data['org_name_en'] ?? null,
                'license_number' => $data['license_number'],
                'license_authority' => $data['license_authority'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
                'phone' => $data['org_phone'],
                'email' => $data['org_email'],
                'website' => $data['website'] ?? null,
                'status' => 'pending',
            ]);

            $user = User::create([
                'name' => $data['manager_name'],
                'email' => $data['manager_email'],
                'phone' => $data['manager_phone'],
                'password' => Hash::make($data['password']),
                'locale' => 'ar',
                'status' => 'pending',
                'primary_organization_id' => $organization->id,
            ]);

            $user->assignRole(config('makeen.roles.association_manager'));

            return $organization;
        });

        // Notify every super_admin so the admin team sees the new
        // registration immediately in their bell + inbox. Wrapped in
        // try/catch because misconfigured SMTP must NOT block the
        // visitor's success flow.
        try {
            $adminRole = Role::query()->where('name', config('makeen.roles.super_admin'))->first();

            if ($adminRole !== null) {
                $admins = $adminRole->users()->get();

                if ($admins->isNotEmpty()) {
                    NotificationFacade::send(
                        $admins,
                        new AssociationRegisteredNotification(
                            $org,
                            $data['manager_name'] ?? null,
                            $data['manager_email'] ?? null,
                        ),
                    );
                }
            }
        } catch (Throwable $e) {
            Log::error('AssociationRegistration: notify admins failed', [
                'organization_id' => $org->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Session flag so the standalone pending page cannot be
        // opened directly via a copied URL. Auto-expires after one
        // request (we read it with pull()).
        session()->flash('registration.pending', [
            'organization' => $org->name_ar,
            'email' => $data['manager_email'] ?? null,
        ]);

        return redirect()->route('register.association.pending');
    }

    public function pending(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('registration.pending')) {
            // No active pending-registration context — bounce to the
            // registration form instead of showing a generic
            // "thank you" screen to random visitors.
            return redirect()->route('register.association.show');
        }

        $context = $request->session()->pull('registration.pending');

        return view('auth.register.association-pending', [
            'context' => is_array($context) ? $context : null,
        ]);
    }
}
