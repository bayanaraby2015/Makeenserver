<?php

namespace App\Filament\Resources\Initiatives\Pages;

use App\Filament\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use Filament\Resources\Pages\Page;

class PrintInitiative extends Page
{
    protected static string $resource = InitiativeResource::class;

    protected string $view = 'filament.initiatives.print';

    public ?int $initiativeId = null;

    public function mount(int|string $record): void
    {
        $this->initiativeId = (int) $record;
    }

    public function getRecord(): Initiative
    {
        /** @var Initiative $initiative */
        $initiative = Initiative::query()
            ->with(['organization', 'outputs', 'milestones', 'payments', 'risks', 'kpiValues.definition', 'evaluations.evaluator'])
            ->findOrFail($this->initiativeId);

        return $initiative;
    }

    public function getTitle(): string
    {
        return (app()->isLocale('ar') ? 'ملف المبادرة' : 'Initiative brief').' - '.$this->getRecord()->name_ar;
    }
}
