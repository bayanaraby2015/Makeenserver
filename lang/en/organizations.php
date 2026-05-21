<?php

return [
    'navigation_label' => 'Organizations',
    'model_label' => 'Organization',
    'plural_model_label' => 'Organizations',
    'navigation_group' => 'Administration',

    'tabs' => [
        'all' => 'All',
        'pending' => 'Pending review',
        'active' => 'Approved',
        'suspended' => 'Suspended',
        'archived' => 'Archived',
        'rejected' => 'Rejected',
    ],

    'fields' => [
        'type' => 'Type',
        'name_ar' => 'Name (Arabic)',
        'name_en' => 'Name (English)',
        'license_number' => 'License / Registry No.',
        'license_authority' => 'Issuing authority',
        'city' => 'City / Region',
        'address' => 'Address',
        'phone' => 'Phone',
        'email' => 'Email',
        'website' => 'Website',
        'status' => 'Status',
        'approved_at' => 'Approved at',
        'approved_by' => 'Approved by',
        'rejection_reason' => 'Rejection reason',
        'rejected_at' => 'Rejected at',
        'rejected_by' => 'Rejected by',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'members_count' => 'Members',
    ],

    'sections' => [
        'identity' => 'Identity',
        'license' => 'License',
        'contact' => 'Contact',
        'lifecycle' => 'Status & review',
    ],

    'types' => [
        'association' => 'Association',
        'donor' => 'Donor',
        'excellence_team' => 'Excellence team',
        'consultant_firm' => 'Consultant firm',
    ],

    'statuses' => [
        'pending' => 'Pending review',
        'active' => 'Approved',
        'suspended' => 'Suspended',
        'archived' => 'Archived',
        'rejected' => 'Rejected',
    ],

    'actions' => [
        'approve' => 'Approve',
        'approve_modal_heading' => 'Approve organization',
        'approve_modal_description' => 'This will activate the account and email the contact person.',
        'approve_success' => 'Organization approved and email notification sent.',

        'reject' => 'Reject',
        'reject_modal_heading' => 'Reject organization',
        'reject_modal_description' => 'Please provide a rejection reason. It will be included in the email.',
        'reject_reason_label' => 'Reason',
        'reject_reason_placeholder' => 'e.g., the commercial registry is expired or unclear.',
        'reject_success' => 'Organization rejected and notification email sent.',

        'suspend' => 'Suspend',
        'suspend_success' => 'Organization suspended.',
        'reactivate' => 'Re-activate',
        'reactivate_success' => 'Organization re-activated.',

        'activate_manager' => 'Activate manager',
        'activate_manager_modal_heading' => 'Activate organization manager',
        'activate_manager_modal_description' => 'Flip any pending manager account(s) on this organization to active so they can log in immediately.',
        'activate_manager_success' => ':count manager account(s) activated.',
    ],
];
