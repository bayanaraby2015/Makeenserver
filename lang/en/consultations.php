<?php

return [
    'navigation_label' => 'Consultations & Tickets',
    'model_label' => 'Consultation',
    'plural_model_label' => 'Consultations & Tickets',

    'fields' => [
        'requester_organization' => 'Requesting Organization',
        'initiative' => 'Initiative',
        'consultant' => 'Consultant',
        'responsible_user' => 'Responsible person',
        'request_type' => 'Request type',
        'routing_target' => 'Routing target',
        'specialization' => 'Consultation Type',
        'subject' => 'Subject',
        'details' => 'Details',
        'attachments' => 'Attachments',
        'status' => 'Status',
        'scheduled_at' => 'Scheduled At',
        'scheduled_date' => 'Session Date',
        'scheduled_time' => 'Session Time',
        'scheduled_hour' => 'Hour',
        'scheduled_minute' => 'Minute',
        'scheduled_period' => 'Period',
        'closed_at' => 'Closed At',
        'rejection_reason' => 'Rejection Reason',
        'note' => 'Note',
        'reply' => 'Reply',
        'closing_note' => 'Closing summary and recommendations',
        'note_author' => 'Author',
        'created_at' => 'Created At',
        'meeting_url' => 'Meeting Link',
        'meeting_provider' => 'Meeting Provider',
        'meeting_password' => 'Meeting Password',
        'create_zoom_meeting' => 'Create Zoom Meeting',
    ],

    'statuses' => [
        'requested' => 'Requested',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'actions' => [
        'accept' => 'Accept',
        'reject' => 'Reject',
        'schedule' => 'Schedule',
        'complete' => 'Complete',
        'add_note' => 'Add Note',
        'reply' => 'Add Reply',
        'request_from_initiative' => 'Request Consultation',
        'view_calendar' => 'View Calendar',
    ],

    'messages' => [
        'created' => 'Consultation request sent successfully.',
        'accepted' => 'Consultation accepted.',
        'rejected' => 'Consultation rejected.',
        'scheduled' => 'Consultation schedule updated.',
        'completed' => 'Consultation closed successfully.',
        'note_added' => 'Note added successfully.',
        'reply_added' => 'Reply added successfully.',
        'manual_meeting_url' => 'Leave empty to use the generated Zoom link when Zoom is configured.',
        'closing_note_help' => 'Closing the session requires documenting the consultant recommendations or session summary.',
        'responsible_user_help' => 'Optional: route the request directly to the project manager, specialist, or consultant.',
        'no_notes' => 'No notes have been added yet.',
    ],

    'request_types' => [
        'consultation' => 'Consultation',
        'question' => 'Question / Ticket',
        'support' => 'Consulting support request',
        'appointment' => 'Appointment request',
    ],

    'routing_targets' => [
        'project_manager' => 'Project manager',
        'specialist' => 'Specialist',
        'consultant' => 'Consultant',
    ],

    'sections' => [
        'details' => 'Consultation Details',
        'session' => 'Session & Meeting',
        'notes' => 'Notes, Replies and Recommendations',
    ],

    'calendar' => [
        'title' => 'Consultation Calendar',
        'today' => 'Today',
        'month' => 'Month',
        'week' => 'Week',
        'day' => 'Day',
        'list' => 'List',
        'empty' => 'No scheduled consultations.',
    ],

    'time' => [
        'am' => 'AM',
        'pm' => 'PM',
    ],
];
