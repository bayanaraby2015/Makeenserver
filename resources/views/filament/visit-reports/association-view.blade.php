<x-filament-panels::page>
    @include('filament.visit-reports.partials.show', [
        'record' => $this->getRecord(),
        'context' => 'association',
    ])
</x-filament-panels::page>
