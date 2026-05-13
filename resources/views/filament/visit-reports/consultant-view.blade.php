<x-filament-panels::page>
    @include('filament.visit-reports.partials.show', [
        'record' => $this->getRecord(),
        'context' => 'consultant',
    ])
</x-filament-panels::page>
