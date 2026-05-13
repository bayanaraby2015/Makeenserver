<?php

return [
    'navigation_label' => 'Activity log',
    'model_label' => 'Entry',
    'plural_model_label' => 'Activity log',
    'navigation_group' => 'Administration',

    'fields' => [
        'log_name' => 'Log',
        'description' => 'Description',
        'event' => 'Event',
        'subject_type' => 'Subject',
        'subject_id' => 'Subject ID',
        'causer' => 'Caused by',
        'properties' => 'Properties',
        'created_at' => 'Time',
    ],

    'events' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
    ],

    'logs' => [
        'default' => 'System',
        'auth' => 'Authentication',
        'organization' => 'Organizations',
        'user' => 'Users',
        'role' => 'Roles',
        'initiative' => 'Initiatives',
        'initiative_evaluations' => 'Initiative evaluations',
        'consultations' => 'Consultations',
        'consultation_notes' => 'Consultation replies and notes',
        'service_evaluations' => 'Service evaluations',
        'donor_interest' => 'Donor interests',
    ],

    'models' => [
        'Initiative' => 'Initiative',
        'Organization' => 'Organization',
        'User' => 'User',
        'Role' => 'Role',
        'Permission' => 'Permission',
        'Consultation' => 'Consultation',
        'ConsultationNote' => 'Consultation reply/note',
        'InitiativeOutput' => 'Initiative output',
        'InitiativeMilestone' => 'Initiative milestone',
        'InitiativePayment' => 'Initiative payment',
        'InitiativeRisk' => 'Initiative risk',
        'InitiativeEvaluation' => 'Initiative evaluation',
        'ServiceEvaluation' => 'Service evaluation',
        'InitiativeKpiValue' => 'KPI value',
        'DonorInterest' => 'Donor interest',
        'KpiDefinition' => 'KPI definition',
        'Activity' => 'Activity log',
    ],
];
