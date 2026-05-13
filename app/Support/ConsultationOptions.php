<?php

namespace App\Support;

class ConsultationOptions
{
    /**
     * @return array<string, string>
     */
    public static function requestTypes(): array
    {
        return [
            'consultation' => __('consultations.request_types.consultation'),
            'question' => __('consultations.request_types.question'),
            'support' => __('consultations.request_types.support'),
            'appointment' => __('consultations.request_types.appointment'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function routingTargets(): array
    {
        return [
            'project_manager' => __('consultations.routing_targets.project_manager'),
            'specialist' => __('consultations.routing_targets.specialist'),
            'consultant' => __('consultations.routing_targets.consultant'),
        ];
    }

    public static function requestTypeLabel(?string $type): string
    {
        return self::requestTypes()[$type ?? ''] ?? __('consultations.request_types.consultation');
    }

    public static function routingTargetLabel(?string $target): string
    {
        return self::routingTargets()[$target ?? ''] ?? '-';
    }
}
