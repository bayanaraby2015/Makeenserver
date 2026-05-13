<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAT (Value-Added Tax)
    |--------------------------------------------------------------------------
    | Saudi Arabia VAT applied to initiative budgets (form section 4).
    */
    'vat_rate' => (float) env('MAKEEN_VAT_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Initiative Duration (months)
    |--------------------------------------------------------------------------
    | Default duration of the implementation/follow-up phase.
    | See README §1 — 32 months for Makeen project phase 3.
    */
    'initiative_duration_months' => (int) env('MAKEEN_INITIATIVE_DURATION_MONTHS', 32),

    /*
    |--------------------------------------------------------------------------
    | Default timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('MAKEEN_DEFAULT_TIMEZONE', 'Asia/Riyadh'),

    /*
    |--------------------------------------------------------------------------
    | Activity log lock
    |--------------------------------------------------------------------------
    | When true, NO user (including super_admin) can delete activity log
    | entries via UI. This is enforced in the activity_log policy.
    | Recommended: true in production, false in local/dev.
    */
    'activity_log_locked' => (bool) env('ACTIVITY_LOG_LOCKED', false),

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    | Canonical role names used throughout the application.
    */
    'roles' => [
        'super_admin' => 'super_admin',
        'excellence_manager' => 'excellence_manager',
        'excellence_member' => 'excellence_member',
        'donor_admin' => 'donor_admin',
        'consultant' => 'consultant',
        'association_manager' => 'association_manager',
        'association_member' => 'association_member',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization types
    |--------------------------------------------------------------------------
    */
    'organization_types' => [
        'association' => 'association',
        'donor' => 'donor',
        'excellence_team' => 'excellence_team',
        'consultant_firm' => 'consultant_firm',
    ],

    'initiative_specializations' => [
        'financial_resources' => 'financial_resources',
        'endowments_investment' => 'endowments_investment',
        'institutional_planning' => 'institutional_planning',
        'developmental_impact' => 'developmental_impact',
    ],
];
