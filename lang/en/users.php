<?php

return [
    'navigation_label' => 'Users',
    'model_label' => 'User',
    'plural_model_label' => 'Users',
    'navigation_group' => 'Administration',

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'password' => 'Password',
        'password_help' => 'Leave blank to keep the current password.',
        'locale' => 'Language',
        'status' => 'Account status',
        'roles' => 'Roles',
        'consultant_specializations' => 'Consultant specializations',
        'primary_organization' => 'Primary organization',
        'last_login_at' => 'Last login',
        'last_login_ip' => 'Last login IP',
        'recent_activity' => 'Recent user activity',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'deleted_at' => 'Deleted at',
        'email_verified_at' => 'Email verified at',
    ],

    'sections' => [
        'identity' => 'Identity',
        'access' => 'Roles & status',
        'security' => 'Security',
        'activity' => 'Activity',
    ],

    'statuses' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'suspended' => 'Suspended',
    ],

    'locales' => [
        'ar' => 'Arabic',
        'en' => 'English',
    ],

    'actions' => [
        'activate' => 'Activate',
        'activate_success' => 'User account activated.',
        'add_consultant_specialization' => 'Add specialization',
        'suspend' => 'Suspend',
        'suspend_success' => 'User account suspended.',
    ],
];
