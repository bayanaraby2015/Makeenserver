<?php

return [
    'navigation_group' => 'Administration',
    'navigation_label' => 'Zoom Settings',
    'model_label' => 'Zoom Setting',
    'plural_model_label' => 'Zoom Settings',

    'fields' => [
        'configured' => 'Configured',
        'account_id' => 'Account ID',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'user_id' => 'User ID',
        'user_id_help' => 'Enter "me" to use the account that created the app, or a Zoom user e-mail. Do not put the platform name here.',
        'default_duration' => 'Default Meeting Duration',
        'minutes' => 'minutes',
        'updated_at' => 'Last Updated',
    ],

    'actions' => [
        'test_connection' => 'Test Zoom Connection',
    ],

    'test' => [
        'not_configured' => 'Zoom settings have not been saved yet.',
        'token_failed' => 'Failed to obtain an access token from Zoom. Check Account ID, Client ID and Client Secret.',
        'user_failed' => 'Zoom user ":user" not found. Use "me" or a valid Zoom user e-mail.',
        'success' => 'Zoom connection successful ✓',
        'success_detail' => 'Found user: :name (:email)',
        'error_detail' => 'Status: :status — :body',
    ],
];
