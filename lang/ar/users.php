<?php

return [
    'navigation_label' => 'المستخدمون',
    'model_label' => 'مستخدم',
    'plural_model_label' => 'المستخدمون',
    'navigation_group' => 'الإدارة',

    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الجوال',
        'password' => 'كلمة المرور',
        'password_help' => 'اتركها فارغة إذا لم ترغب بتغييرها.',
        'locale' => 'اللغة',
        'status' => 'حالة الحساب',
        'roles' => 'الأدوار',
        'consultant_specializations' => 'تخصصات المستشار',
        'primary_organization' => 'الجهة الرئيسية',
        'last_login_at' => 'آخر تسجيل دخول',
        'last_login_ip' => 'آخر عنوان IP',
        'recent_activity' => 'آخر نشاطات المستخدم',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'آخر تحديث',
        'deleted_at' => 'تاريخ الحذف',
        'email_verified_at' => 'تاريخ تأكيد البريد',
    ],

    'sections' => [
        'identity' => 'الهوية',
        'access' => 'الصلاحيات والحالة',
        'security' => 'الأمان',
        'activity' => 'النشاط',
    ],

    'statuses' => [
        'pending' => 'قيد المراجعة',
        'active' => 'نشط',
        'suspended' => 'موقوف',
    ],

    'locales' => [
        'ar' => 'العربية',
        'en' => 'English',
    ],

    'actions' => [
        'activate' => 'تفعيل',
        'activate_success' => 'تم تفعيل الحساب.',
        'add_consultant_specialization' => 'إضافة تخصص',
        'suspend' => 'إيقاف',
        'suspend_success' => 'تم إيقاف الحساب.',
    ],
];
