<?php

namespace App\Support;

class ServiceEvaluationOptions
{
    /**
     * @return array<string, string>
     */
    public static function serviceTypes(): array
    {
        return [
            'initiative' => 'مبادرة',
            'consultation' => 'استشارة',
            'visit_report' => 'زيارة',
            'monthly_report' => 'تقرير شهري',
            'platform' => 'خدمة المنصة',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function ratings(): array
    {
        return [
            1 => '1 - ضعيف',
            2 => '2 - مقبول',
            3 => '3 - جيد',
            4 => '4 - جيد جداً',
            5 => '5 - ممتاز',
        ];
    }

    public static function serviceTypeLabel(?string $type): string
    {
        if ($type === 'visit') {
            return 'زيارة';
        }

        return self::serviceTypes()[$type ?? ''] ?? '-';
    }

    public static function ratingLabel(mixed $rating): string
    {
        $rating = (int) $rating;

        return self::ratings()[$rating] ?? '-';
    }
}
