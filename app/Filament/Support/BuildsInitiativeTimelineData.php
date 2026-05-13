<?php

namespace App\Filament\Support;

use App\Models\Initiative;
use App\Models\InitiativeMilestone;
use App\Models\InitiativePayment;
use Carbon\Carbon;

trait BuildsInitiativeTimelineData
{
    abstract public function getRecord(): Initiative;

    /**
     * @return array{
     *   today: string,
     *   range: array{start: string, end: string, days: int},
     *   phases: array<int, array{id: string, name: string, start: string, end: string, progress: int, status: string, total_cost: float}>,
     *   payments: array<int, array{date: string, amount: float, percentage: float|null}>
     * }
     */
    public function getGanttData(): array
    {
        $initiative = $this->getRecord();
        $today = now()->startOfDay();

        $phases = [];
        $phaseStarts = [];
        $phaseEnds = [];

        foreach ($initiative->milestones()->orderBy('order_index')->get() as $milestone) {
            /** @var InitiativeMilestone $milestone */
            if ($milestone->start_date === null || $milestone->end_date === null) {
                continue;
            }

            $start = $milestone->start_date->copy()->startOfDay();
            $end = $milestone->end_date->copy()->startOfDay();
            $phaseStarts[] = $start;
            $phaseEnds[] = $end;

            $status = $today->lt($start)
                ? 'open'
                : ($today->gt($end) ? 'done' : 'in_progress');

            $totalDuration = max(1, $start->diffInDays($end) + 1);
            $elapsedDays = $today->lte($start)
                ? 0
                : min($totalDuration, $start->diffInDays(min($today, $end)) + 1);
            $progress = $status === 'done'
                ? 100
                : ($status === 'open' ? 0 : (int) round(($elapsedDays / $totalDuration) * 100));

            $phases[] = [
                'id' => 'milestone-'.$milestone->id,
                'name' => $milestone->phase,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'progress' => $progress,
                'status' => $status,
                'total_cost' => (float) $milestone->total_cost,
            ];
        }

        $payments = [];
        foreach ($initiative->payments()->orderBy('order_index')->get() as $payment) {
            /** @var InitiativePayment $payment */
            if ($payment->due_date === null) {
                continue;
            }

            $payments[] = [
                'date' => $payment->due_date->toDateString(),
                'amount' => (float) $payment->amount,
                'percentage' => $payment->percentage !== null ? (float) $payment->percentage : null,
            ];
        }

        $startCandidates = array_filter([
            $initiative->start_date?->copy()->startOfDay(),
            ...$phaseStarts,
        ]);
        $endCandidates = array_filter([
            $initiative->end_date?->copy()->startOfDay(),
            ...$phaseEnds,
        ]);

        $rangeStart = ! empty($startCandidates)
            ? collect($startCandidates)->sort()->first()
            : $today->copy();
        $rangeEnd = ! empty($endCandidates)
            ? collect($endCandidates)->sortDesc()->first()
            : $today->copy()->addDays(30);

        if ($rangeEnd->lt($rangeStart)) {
            $rangeEnd = $rangeStart->copy();
        }

        return [
            'today' => $today->toDateString(),
            'range' => [
                'start' => $rangeStart->toDateString(),
                'end' => $rangeEnd->toDateString(),
                'days' => $rangeStart->diffInDays($rangeEnd) + 1,
            ],
            'phases' => $phases,
            'payments' => $payments,
        ];
    }
}

