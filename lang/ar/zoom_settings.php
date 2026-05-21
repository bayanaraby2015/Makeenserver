<?php

return [
    'navigation_group' => 'الإدارة',
    'navigation_label' => 'إعدادات Zoom',
    'model_label' => 'إعداد Zoom',
    'plural_model_label' => 'إعدادات Zoom',

    'fields' => [
        'configured' => 'مكتمل الإعداد',
        'account_id' => 'معرّف الحساب',
        'client_id' => 'معرّف العميل',
        'client_secret' => 'المفتاح السري',
        'user_id' => 'معرّف المستخدم',
        'user_id_help' => 'اكتب me لاستخدام الحساب المالك للتطبيق، أو ضع البريد الإلكتروني لمستخدم Zoom. لا تضع اسم المنصة هنا.',
        'default_duration' => 'المدة الافتراضية للاجتماع',
        'minutes' => 'دقيقة',
        'updated_at' => 'آخر تحديث',
    ],

    'actions' => [
        'test_connection' => 'اختبار الاتصال بـ Zoom',
    ],

    'test' => [
        'not_configured' => 'لم يتم حفظ إعدادات Zoom بعد.',
        'token_failed' => 'فشل الحصول على access token من Zoom. تحقق من Account ID و Client ID و Client Secret.',
        'user_failed' => 'لم يتم العثور على المستخدم ":user" في Zoom. اكتب me أو بريداً إلكترونياً لمستخدم Zoom حقيقي.',
        'success' => 'الاتصال بـ Zoom ناجح ✓',
        'success_detail' => 'تم العثور على المستخدم: :name (:email)',
        'error_detail' => 'الحالة: :status — :body',
    ],
];
