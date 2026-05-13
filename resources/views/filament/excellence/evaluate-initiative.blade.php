<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-end gap-2">
            <x-filament::button type="submit" color="primary">
                {{ __('initiatives.actions.save_evaluation') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
