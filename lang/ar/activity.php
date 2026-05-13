<?php

return [
    'navigation_label' => 'سجل النشاط',
    'model_label' => 'سجل',
    'plural_model_label' => 'سجل النشاط',
    'navigation_group' => 'الإدارة',

    'fields' => [
        'log_name' => 'النطاق',
        'description' => 'الوصف',
        'event' => 'الحدث',
        'subject_type' => 'العنصر المتأثر',
        'subject_id' => 'معرف العنصر',
        'causer' => 'المنفذ',
        'properties' => 'البيانات',
        'created_at' => 'الوقت',
    ],

    'events' => [
        'created' => 'إنشاء',
        'updated' => 'تحديث',
        'deleted' => 'حذف',
        'restored' => 'استرجاع',
    ],

    'logs' => [
        'default' => 'النظام',
        'auth' => 'المصادقة',
        'organization' => 'الجمعيات والجهات',
        'user' => 'المستخدمون',
        'role' => 'الأدوار',
        'initiative' => 'المبادرات',
        'initiative_evaluations' => 'تقييمات المبادرات',
        'consultations' => 'الاستشارات',
        'consultation_notes' => 'ردود وملاحظات الاستشارات',
        'service_evaluations' => 'تقييمات الخدمة',
        'donor_interest' => 'اهتمامات الجهات المانحة',
    ],

    'models' => [
        'Initiative' => 'مبادرة',
        'Organization' => 'جمعية/جهة',
        'User' => 'مستخدم',
        'Role' => 'دور',
        'Permission' => 'صلاحية',
        'Consultation' => 'استشارة',
        'ConsultationNote' => 'رد/ملاحظة استشارة',
        'InitiativeOutput' => 'مخرج مبادرة',
        'InitiativeMilestone' => 'مرحلة مبادرة',
        'InitiativePayment' => 'دفعة مبادرة',
        'InitiativeRisk' => 'خطر مبادرة',
        'InitiativeEvaluation' => 'تقييم مبادرة',
        'ServiceEvaluation' => 'تقييم خدمة',
        'InitiativeKpiValue' => 'قيمة مؤشر',
        'DonorInterest' => 'اهتمام جهة مانحة',
        'KpiDefinition' => 'تعريف مؤشر',
        'Activity' => 'سجل نشاط',
    ],
];
