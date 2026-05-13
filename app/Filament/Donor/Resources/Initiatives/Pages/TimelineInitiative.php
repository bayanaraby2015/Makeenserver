<?php

namespace App\Filament\Donor\Resources\Initiatives\Pages;

use App\Filament\Donor\Resources\Initiatives\InitiativeResource;
use App\Filament\Support\BuildsInitiativeTimelineData;
use App\Models\Initiative;
use App\Support\DisplayNumber;
use Filament\Resources\Pages\Page;

class TimelineInitiative extends Page
{
    use BuildsInitiativeTimelineData;

    protected static string $resource = InitiativeResource::class;

    protected string $view = 'filament.initiatives.timeline';

    public ?int $initiativeId = null;

    public function mount(int|string $record): void
    {
        $this->initiativeId = (int) $record;
    }

    public function getRecord(): Initiative
    {
        /** @var Initiative $initiative */
        $initiative = Initiative::query()->findOrFail($this->initiativeId);

        return $initiative;
    }

    public function getTitle(): string
    {
        return __('initiatives.gantt.title').' - '.$this->getRecord()->name_ar;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarEvents(): array
    {
        $events = [];
        $initiative = $this->getRecord();

        if ($initiative->start_date !== null) {
            $events[] = [
                'title' => __('initiatives.calendar.start_date').' - '.$initiative->name_ar,
                'start' => $initiative->start_date->toDateString(),
                'color' => '#283979',
            ];
        }

        if ($initiative->end_date !== null) {
            $events[] = [
                'title' => __('initiatives.calendar.end_date').' - '.$initiative->name_ar,
                'start' => $initiative->end_date->toDateString(),
                'color' => '#283979',
            ];
        }

        foreach ($initiative->milestones()->orderBy('order_index')->get() as $milestone) {
            if ($milestone->start_date !== null) {
                $events[] = [
                    'title' => __('initiatives.calendar.milestone').' - '.$milestone->phase,
                    'start' => $milestone->start_date->toDateString(),
                    'end' => $milestone->end_date?->toDateString(),
                    'color' => '#4F46E5',
                ];
            }
        }

        foreach ($initiative->payments()->orderBy('order_index')->get() as $payment) {
            if ($payment->due_date !== null) {
                $events[] = [
                    'title' => __('initiatives.calendar.payment').' - '.DisplayNumber::riyal($payment->amount),
                    'start' => $payment->due_date->toDateString(),
                    'color' => '#0EA5E9',
                ];
            }
        }

        return $events;
    }
}
