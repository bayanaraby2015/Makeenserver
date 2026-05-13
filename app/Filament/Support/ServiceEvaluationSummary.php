<?php

namespace App\Filament\Support;

use App\Models\ServiceEvaluation;
use App\Support\ServiceEvaluationOptions;
use Illuminate\Support\HtmlString;

class ServiceEvaluationSummary
{
    public static function render(string $serviceType, int|string|null $serviceId): HtmlString
    {
        if ($serviceId === null) {
            return new HtmlString('<div class="text-sm text-gray-500">لا توجد تقييمات خدمة حتى الآن.</div>');
        }

        $evaluations = ServiceEvaluation::query()
            ->with('evaluator')
            ->where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->latest('evaluated_at')
            ->latest('created_at')
            ->get();

        if ($evaluations->isEmpty()) {
            return new HtmlString('<div class="text-sm text-gray-500">لا توجد تقييمات خدمة حتى الآن.</div>');
        }

        $average = number_format((float) $evaluations->avg('rating'), 1);
        $count = $evaluations->count();
        $items = $evaluations->map(function (ServiceEvaluation $evaluation): string {
            $author = e($evaluation->evaluator?->name ?? '-');
            $rating = e(ServiceEvaluationOptions::ratingLabel($evaluation->rating));
            $date = e(optional($evaluation->evaluated_at ?? $evaluation->created_at)->format('Y-m-d h:i A') ?? '-');
            $comments = trim(strip_tags((string) $evaluation->comments));
            $comments = $comments !== '' ? e($comments) : '-';

            return <<<HTML
                <div style="border:1px solid #e7ecf3;border-radius:12px;padding:12px;background:#fff;">
                    <div style="display:flex;gap:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
                        <strong style="color:#283979;">{$author}</strong>
                        <span style="background:rgba(33,178,184,.12);color:#283979;border-radius:999px;padding:4px 10px;font-weight:800;">{$rating}</span>
                    </div>
                    <div style="color:#647085;font-size:12px;margin-top:4px;">{$date}</div>
                    <div style="color:#2b354f;line-height:1.8;margin-top:8px;">{$comments}</div>
                </div>
            HTML;
        })->implode('');

        return new HtmlString(<<<HTML
            <div style="display:grid;gap:12px;direction:rtl;">
                <div style="display:flex;gap:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;border:1px solid #dbeafe;background:linear-gradient(135deg,rgba(40,57,121,.07),rgba(33,178,184,.08));border-radius:14px;padding:12px 14px;">
                    <strong style="color:#283979;">متوسط تقييم الخدمة</strong>
                    <span style="background:#f9ad1c;color:#283979;border-radius:999px;padding:6px 12px;font-weight:900;">{$average}/5 · {$count}</span>
                </div>
                {$items}
            </div>
        HTML);
    }
}
