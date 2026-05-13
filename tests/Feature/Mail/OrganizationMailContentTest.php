<?php

use App\Mail\OrganizationApprovedMail;
use App\Mail\OrganizationRejectedMail;
use App\Models\Organization;

it('renders the approval mail with Arabic subject and body', function () {
    $org = Organization::factory()->make([
        'name_ar' => 'جمعية الاختبار',
        'email' => 'org@test.local',
    ]);

    $mail = new OrganizationApprovedMail($org);
    $rendered = $mail->render();

    expect($mail->envelope()->subject)->toContain('جمعية الاختبار')
        ->and($rendered)
        ->toContain('جمعية الاختبار')
        ->toContain('الدخول إلى لوحة الجمعية');
});

it('renders the rejection mail with the supplied reason and Arabic body', function () {
    $org = Organization::factory()->make([
        'name_ar' => 'جمعية الاختبار',
        'email' => 'org@test.local',
    ]);

    $mail = new OrganizationRejectedMail($org, 'سبب الرفض هو السجل التجاري');
    $rendered = $mail->render();

    expect($mail->envelope()->subject)->toContain('جمعية الاختبار')
        ->and($mail->reason)->toBe('سبب الرفض هو السجل التجاري')
        ->and($rendered)->toContain('سبب الرفض هو السجل التجاري');
});
