<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\AssociationRegistrationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

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
 *   4. We redirect to /register/association/pending which tells the
 *      visitor that an admin will review and activate the account.
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

        DB::transaction(function () use ($data) {
            $org = Organization::create([
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
                'primary_organization_id' => $org->id,
            ]);

            $user->assignRole(config('makeen.roles.association_manager'));
        });

        return redirect()->route('register.association.pending');
    }

    public function pending(): View
    {
        return view('auth.register.association-pending');
    }
}
