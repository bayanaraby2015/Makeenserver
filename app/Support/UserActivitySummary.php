<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\Models\Activity;

class UserActivitySummary
{
    public static function render(User $user, int $limit = 8): HtmlString
    {
        $activities = Activity::query()
            ->where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->getKey())
            ->latest()
            ->limit($limit)
            ->get();

        if ($activities->isEmpty()) {
            return new HtmlString(
                self::styles()
                .'<div class="mk-user-activity mk-user-activity--empty">'
                .'<div class="mk-user-activity__empty">لا توجد نشاطات مسجلة لهذا المستخدم حتى الآن.</div>'
                .'</div>'
            );
        }

        $html = $activities
            ->map(function (Activity $activity): string {
                $time = $activity->created_at?->format('Y-m-d h:i A') ?? '-';
                $log = self::logLabel((string) $activity->log_name);
                $event = self::eventLabel((string) $activity->event);
                $description = $activity->description ?: '-';
                $subject = self::subjectLabel($activity);

                return sprintf(
                    '<article class="mk-user-activity__item">'
                    .'<div class="mk-user-activity__marker"></div>'
                    .'<div class="mk-user-activity__content">'
                    .'<div class="mk-user-activity__top">'
                    .'<span class="mk-user-activity__event">%s</span>'
                    .'<time>%s</time>'
                    .'</div>'
                    .'<strong>%s</strong>'
                    .'<p>%s</p>'
                    .'<div class="mk-user-activity__meta"><span>%s</span><span>%s</span></div>'
                    .'</div>'
                    .'</article>',
                    e($event),
                    e($time),
                    e($log),
                    e($description),
                    e('النطاق: '.$log),
                    e('العنصر: '.$subject),
                );
            })
            ->implode('');

        return new HtmlString(self::styles().'<div class="mk-user-activity">'.$html.'</div>');
    }

    private static function styles(): string
    {
        return <<<'HTML'
<style>
    .mk-user-activity {
        direction: rtl;
        display: grid;
        gap: 0;
        padding: 4px 0;
        position: relative;
    }

    .mk-user-activity::before {
        background: linear-gradient(180deg, #21b2b8, rgba(40, 57, 121, .1));
        border-radius: 999px;
        content: "";
        inset-block: 14px;
        inset-inline-start: 12px;
        position: absolute;
        width: 3px;
    }

    .mk-user-activity--empty::before {
        display: none;
    }

    .mk-user-activity__empty {
        background: linear-gradient(135deg, rgba(33, 178, 184, .08), rgba(249, 173, 28, .08));
        border: 1px solid #dbe7f0;
        border-radius: 14px;
        color: #2b354f;
        font-size: 13px;
        font-weight: 800;
        padding: 18px;
        text-align: center;
    }

    .mk-user-activity__item {
        display: grid;
        gap: 12px;
        grid-template-columns: 28px minmax(0, 1fr);
        padding: 0 0 12px;
        position: relative;
    }

    .mk-user-activity__marker {
        background: #fff;
        border: 3px solid #21b2b8;
        border-radius: 999px;
        box-shadow: 0 0 0 5px rgba(33, 178, 184, .1);
        height: 14px;
        margin: 18px auto 0;
        position: relative;
        width: 14px;
        z-index: 1;
    }

    .mk-user-activity__content {
        background: #fff;
        border: 1px solid #e6edf4;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(43, 53, 79, .06);
        padding: 13px 14px;
    }

    .mk-user-activity__top,
    .mk-user-activity__meta {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
    }

    .mk-user-activity__event {
        background: #eef6ff;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        color: #283979;
        font-size: 11px;
        font-weight: 900;
        padding: 4px 9px;
    }

    .mk-user-activity time {
        color: #667085;
        direction: ltr;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-user-activity strong {
        color: #2b354f;
        display: block;
        font-size: 14px;
        font-weight: 900;
        margin-top: 9px;
    }

    .mk-user-activity p {
        color: #344054;
        font-size: 13px;
        line-height: 1.7;
        margin: 6px 0 0;
    }

    .mk-user-activity__meta {
        justify-content: flex-start;
        margin-top: 10px;
    }

    .mk-user-activity__meta span {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 999px;
        color: #667085;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 8px;
    }
</style>
HTML;
    }

    private static function eventLabel(string $event): string
    {
        return match ($event) {
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            'restored' => 'استعادة',
            default => $event !== '' ? $event : 'نشاط',
        };
    }

    private static function logLabel(string $logName): string
    {
        return match ($logName) {
            'default' => 'النظام',
            'auth' => 'المصادقة',
            'organization' => 'الجمعيات والجهات',
            'user', 'users' => 'المستخدمون',
            'role', 'roles' => 'الأدوار والصلاحيات',
            'initiative', 'initiatives' => 'المبادرات',
            'initiative_evaluations' => 'تقييمات المبادرات',
            'consultations' => 'الاستشارات',
            'consultation_notes' => 'ردود الاستشارات',
            'visit_reports' => 'تقارير الزيارات',
            'monthly_reports' => 'التقارير الشهرية',
            'service_evaluations' => 'تقييم الخدمة',
            'donor_interest' => 'اهتمامات الجهات المانحة',
            default => $logName !== '' ? $logName : 'النظام',
        };
    }

    private static function subjectLabel(Activity $activity): string
    {
        $type = class_basename((string) $activity->subject_type);
        $id = $activity->subject_id;

        $label = match ($type) {
            'Initiative' => 'مبادرة',
            'Organization' => 'جهة',
            'User' => 'مستخدم',
            'Role' => 'دور',
            'Permission' => 'صلاحية',
            'Consultation' => 'استشارة',
            'ConsultationNote' => 'رد استشارة',
            'VisitReport' => 'تقرير زيارة',
            'MonthlyReport' => 'تقرير شهري',
            'ServiceEvaluation' => 'تقييم خدمة',
            default => $type !== '' ? $type : 'غير محدد',
        };

        return $id ? $label.' #'.$id : $label;
    }
}
