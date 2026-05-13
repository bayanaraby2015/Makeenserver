<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Public association self-registration form validation.
 *
 * Default Sprint 1 field set. The product owner can adjust freely;
 * validation rules below are the single source of truth and the
 * blade view derives its inputs from them.
 */
class AssociationRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public route — no auth required.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Organization
            'org_name_ar' => ['required', 'string', 'max:255'],
            'org_name_en' => ['nullable', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:64'],
            'license_authority' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'org_phone' => ['required', 'string', 'max:32'],
            'org_email' => [
                'required', 'email:rfc', 'max:255',
                Rule::unique('organizations', 'email'),
            ],
            'website' => ['nullable', 'url', 'max:255'],

            // Manager (becomes the first user)
            'manager_name' => ['required', 'string', 'max:255'],
            'manager_phone' => ['required', 'string', 'max:32'],
            'manager_email' => [
                'required', 'email:rfc', 'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],

            // Legal
            'accept_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'org_name_ar' => __('register.fields.org_name_ar'),
            'org_name_en' => __('register.fields.org_name_en'),
            'license_number' => __('register.fields.license_number'),
            'license_authority' => __('register.fields.license_authority'),
            'city' => __('register.fields.city'),
            'address' => __('register.fields.address'),
            'org_phone' => __('register.fields.org_phone'),
            'org_email' => __('register.fields.org_email'),
            'website' => __('register.fields.website'),
            'manager_name' => __('register.fields.manager_name'),
            'manager_phone' => __('register.fields.manager_phone'),
            'manager_email' => __('register.fields.manager_email'),
            'password' => __('register.fields.password'),
            'accept_terms' => __('register.fields.accept_terms'),
        ];
    }
}
