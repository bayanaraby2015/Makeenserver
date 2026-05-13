<?php

return [
    'navigation_label' => 'الاستشارات والتذاكر',
    'model_label' => 'استشارة',
    'plural_model_label' => 'الاستشارات والتذاكر',

    'fields' => [
        'requester_organization' => 'الجهة الطالبة',
        'initiative' => 'المبادرة',
        'consultant' => 'المستشار',
        'responsible_user' => 'المسؤول عن الرد',
        'request_type' => 'نوع الطلب',
        'routing_target' => 'مسار التوجيه',
        'specialization' => 'نوع الاستشارة',
        'subject' => 'موضوع الاستشارة',
        'details' => 'التفاصيل',
        'attachments' => 'المرفقات',
        'status' => 'الحالة',
        'scheduled_at' => 'موعد الجلسة',
        'scheduled_date' => 'تاريخ الجلسة',
        'scheduled_time' => 'وقت الجلسة',
        'scheduled_hour' => 'الساعة',
        'scheduled_minute' => 'الدقيقة',
        'scheduled_period' => 'الفترة',
        'closed_at' => 'تاريخ الإغلاق',
        'rejection_reason' => 'سبب الرفض',
        'note' => 'ملاحظة',
        'reply' => 'الرد',
        'closing_note' => 'ملخص الإغلاق والتوصيات',
        'note_author' => 'كاتب الملاحظة',
        'created_at' => 'تاريخ الإنشاء',
        'meeting_url' => 'رابط الاجتماع',
        'meeting_provider' => 'مزود الاجتماع',
        'meeting_password' => 'كلمة مرور الاجتماع',
        'create_zoom_meeting' => 'إنشاء اجتماع Zoom',
    ],

    'statuses' => [
        'requested' => 'طلب جديد',
        'accepted' => 'مقبولة',
        'rejected' => 'مرفوضة',
        'scheduled' => 'مجدولة',
        'completed' => 'مغلقة',
        'cancelled' => 'ملغاة',
    ],

    'actions' => [
        'accept' => 'قبول',
        'reject' => 'رفض',
        'schedule' => 'تحديد موعد',
        'complete' => 'إغلاق الجلسة',
        'add_note' => 'إضافة ملاحظة',
        'reply' => 'إضافة رد',
        'request_from_initiative' => 'طلب استشارة',
        'view_calendar' => 'عرض التقويم',
    ],

    'messages' => [
        'created' => 'تم إرسال طلب الاستشارة بنجاح.',
        'accepted' => 'تم قبول طلب الاستشارة.',
        'rejected' => 'تم رفض طلب الاستشارة.',
        'scheduled' => 'تم تحديث موعد الاستشارة.',
        'completed' => 'تم إغلاق الاستشارة بنجاح.',
        'note_added' => 'تمت إضافة الملاحظة بنجاح.',
        'reply_added' => 'تم إضافة الرد بنجاح.',
        'manual_meeting_url' => 'اتركه فارغا لاستخدام رابط Zoom المنشأ تلقائيا عند توفر إعدادات Zoom.',
        'closing_note_help' => 'إغلاق الجلسة يتطلب توثيق ملخص الجلسة أو توصيات المستشار.',
        'responsible_user_help' => 'اختياري: يمكن توجيه الطلب مباشرة لمدير المشروع أو المختص أو المستشار.',
        'no_notes' => 'لا توجد ملاحظات مضافة حتى الآن.',
    ],

    'request_types' => [
        'consultation' => 'استشارة',
        'question' => 'سؤال / تذكرة',
        'support' => 'طلب دعم استشاري',
        'appointment' => 'طلب موعد',
    ],

    'routing_targets' => [
        'project_manager' => 'مدير المشروع',
        'specialist' => 'مختص',
        'consultant' => 'مستشار',
    ],

    'sections' => [
        'details' => 'تفاصيل الاستشارة',
        'session' => 'الجلسة والاجتماع',
        'notes' => 'الملاحظات والردود والتوصيات',
    ],

    'calendar' => [
        'title' => 'تقويم الاستشارات',
        'today' => 'اليوم',
        'month' => 'شهر',
        'week' => 'أسبوع',
        'day' => 'يوم',
        'list' => 'قائمة',
        'empty' => 'لا توجد استشارات مجدولة.',
    ],

    'time' => [
        'am' => 'صباحا',
        'pm' => 'مساء',
    ],
];
