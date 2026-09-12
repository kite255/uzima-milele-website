<x-filament-panels::page>
    <form
        wire:submit="save"
        class="space-y-6"
    >
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
            >
                Hifadhi Mipangilio
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>