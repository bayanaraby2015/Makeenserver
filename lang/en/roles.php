<?php

return [
    'navigation_label' => 'Roles & Permissions',
    'model_label' => 'Role',
    'plural_model_label' => 'Roles',
    'navigation_group' => 'Administration',

    'fields' => [
        'name' => 'Role',
        'guard_name' => 'Guard',
        'permissions' => 'Permissions',
        'permissions_count' => 'Permissions',
        'users_count' => 'Users',
        'created_at' => 'Created at',
    ],

    'names' => [
        'super_admin' => 'Super Admin',
        'excellence_manager' => 'Excellence manager',
        'excellence_member' => 'Excellence member',
        'donor_admin' => 'Donor admin',
        'consultant' => 'Consultant',
        'association_manager' => 'Association manager',
        'association_member' => 'Association member',
    ],
];
