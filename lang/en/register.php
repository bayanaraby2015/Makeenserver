<?php

return [
    'page_title' => 'Register a new association',
    'page_subtitle' => 'Register your association to access the Makeen platform services. Our team will review your application and activate your account within a few business days.',

    'sections' => [
        'organization' => 'Organization details',
        'manager' => 'Authorized manager',
        'security' => 'Security & consents',
    ],

    'fields' => [
        'org_name_ar' => 'Organization name (Arabic)',
        'org_name_en' => 'Organization name (English, optional)',
        'license_number' => 'License / registration number',
        'license_authority' => 'Issuing authority',
        'city' => 'City / region',
        'address' => 'Full address',
        'org_phone' => 'Organization phone',
        'org_email' => 'Organization email',
        'website' => 'Website (optional)',

        'manager_name' => 'Authorized manager name',
        'manager_phone' => 'Manager phone',
        'manager_email' => 'Manager email (login)',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'accept_terms' => 'I agree to the terms and the privacy policy',
    ],

    'placeholders' => [
        'website' => 'https://example.org',
        'license_authority' => 'Ministry of Human Resources and Social Development',
    ],

    'submit' => 'Submit registration request',
    'login_link' => 'Already have an account? Sign in',

    'pending' => [
        'title' => 'Your request has been received',
        'body' => 'Thank you for registering on the Makeen platform. Our admin team will review your details and activate your account within a few business days. You will be notified by email upon activation.',
        'home_link' => 'Go to sign-in page',
    ],

    'footer' => [
        'powered_by' => 'Powered by',
    ],
];
