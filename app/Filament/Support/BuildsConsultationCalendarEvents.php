<?php

namespace App\Filament\Support;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\Builder;

trait BuildsConsultationCalendarEvents
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarEvents(): array
    {
        return $this->getConsultationCalendarQuery()
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn (Consultation $consultation): array => [
                'id' => (string) $consultation->id,
                'title' => $this->eventTitle($consultation),
                'start' => $consultation->scheduled_at?->toIso8601String(),
                'url' => $this->eventUrl($consultation),
                'color' => $this->eventColor($consultation->status),
                'extendedProps' => [
                    'status' => __('consultations.statuses.'.$consultation->status),
                    'organization' => $consultation->requesterOrganization?->name_ar,
                    'consultant' => $consultation->consultant?->name,
                    'meeting_url' => $consultation->meeting_url,
                ],
            ])
            ->all();
    }

    abstract protected function getConsultationCalendarQuery(): Builder;

    abstract protected function eventUrl(Consultation $consultation): string;

    protected function eventTitle(Consultation $consultation): string
    {
        return collect([
            $consultation->subject,
            $consultation->requesterOrganization?->name_ar,
        ])->filter(fn (?string $value): bool => filled($value))->implode(' - ');
    }

    protected function eventColor(string $status): string
    {
        return match ($status) {
            'scheduled' => '#283979',
            'completed' => '#10b981',
            'rejected', 'cancelled' => '#ef4444',
            default => '#b88a3a',
        };
    }
}
