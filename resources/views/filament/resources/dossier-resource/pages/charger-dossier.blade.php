<x-filament-panels::page>
    <form wire:submit="charger">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Charger le dossier
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
