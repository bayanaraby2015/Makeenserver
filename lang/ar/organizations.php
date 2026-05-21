<?php

return [
    'navigation_label' => 'الجمعيات والجهات',
    'model_label' => 'جهة',
    'plural_model_label' => 'الجهات',
    'navigation_group' => 'الإدارة',

    'tabs' => [
        'all' => 'الكل',
        'pending' => 'قيد المراجعة',
        'active' => 'معتمدة',
        'suspended' => 'موقوفة',
        'archived' => 'مؤرشفة',
        'rejected' => 'مرفوضة',
    ],

    'fields' => [
        'type' => 'النوع',
        'name_ar' => 'الاسم بالعربية',
        'name_en' => 'الاسم بالإنجليزية',
        'license_number' => 'رقم الترخيص / السجل',
        'license_authority' => 'الجهة المانحة للترخيص',
        'city' => 'المدينة / المنطقة',
        'address' => 'العنوان التفصيلي',
        'phone' => 'هاتف الجهة',
        'email' => 'البريد الإلكتروني',
        'website' => 'الموقع الإلكتروني',
        'status' => 'الحالة',
        'approved_at' => 'تاريخ الاعتماد',
        'approved_by' => 'المعتمِد',
        'rejection_reason' => 'سبب الرفض',
        'rejected_at' => 'تاريخ الرفض',
        'rejected_by' => 'الرافض',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'آخر تحديث',
        'members_count' => 'عدد الأعضاء',
    ],

    'sections' => [
        'identity' => 'الهوية',
        'license' => 'بيانات الترخيص',
        'contact' => 'التواصل',
        'lifecycle' => 'الحالة والمراجعة',
    ],

    'types' => [
        'association' => 'جمعية',
        'donor' => 'جهة مانحة',
        'excellence_team' => 'فريق الإجادة',
        'consultant_firm' => 'شركة استشارات',
    ],

    'statuses' => [
        'pending' => 'قيد المراجعة',
        'active' => 'معتمدة',
        'suspended' => 'موقوفة',
        'archived' => 'مؤرشفة',
        'rejected' => 'مرفوضة',
    ],

    'actions' => [
        'approve' => 'اعتماد',
        'approve_modal_heading' => 'اعتماد الجمعية',
        'approve_modal_description' => 'سيتم تفعيل الحساب وإرسال بريد تنبيه للمدير المسؤول.',
        'approve_success' => 'تم اعتماد الجمعية بنجاح وأُرسل بريد إشعار.',

        'reject' => 'رفض',
        'reject_modal_heading' => 'رفض الجمعية',
        'reject_modal_description' => 'يرجى توضيح سبب الرفض. سيُرسَل سبب الرفض في بريد للمدير.',
        'reject_reason_label' => 'سبب الرفض',
        'reject_reason_placeholder' => 'مثال: السجل التجاري منتهي الصلاحية أو غير واضح.',
        'reject_success' => 'تم رفض الجمعية وأُرسل بريد إشعار بالسبب.',

        'suspend' => 'تعليق',
        'suspend_success' => 'تم تعليق الجهة.',
        'reactivate' => 'إعادة تفعيل',
        'reactivate_success' => 'تم إعادة تفعيل الجهة.',

        'activate_manager' => 'تفعيل حساب المدير',
        'activate_manager_modal_heading' => 'تفعيل حساب مدير الجمعية',
        'activate_manager_modal_description' => 'سيتم تحويل حساب(ات) المدير المسجلة من "في الانتظار" إلى "مُفعّل" فوراً، ليتمكن من تسجيل الدخول.',
        'activate_manager_success' => 'تم تفعيل :count حساب مدير.',
    ],
];
