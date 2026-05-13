<x-filament-panels::page>
    @include('filament.monthly-reports.partials.show', [
        'record' => $this->getRecord(),
    ])
</x-filament-panels::page>
