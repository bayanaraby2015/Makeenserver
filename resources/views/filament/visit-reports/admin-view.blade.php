<x-filament-panels::page>
    @include('filament.visit-reports.partials.show', [
        'record' => $this->getRecord(),
        'context' => 'admin',
    ])
</x-filament-panels::page>
