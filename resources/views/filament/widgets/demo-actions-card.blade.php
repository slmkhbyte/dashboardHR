<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex flex-wrap gap-6 w-full">
                <x-filament::button
                    color="danger"
                    icon="heroicon-o-trash"
                    wire:click="mountAction('clearData')"
                >
                    Kosongkan Data Operasional
                </x-filament::button>

                <x-filament::button
                    color="danger"
                    icon="heroicon-o-exclamation-triangle"
                    wire:click="mountAction('clearDataWithMasterHr')"
                >
                    Kosongkan Semua Data
                </x-filament::button>

                <x-filament::button
                    color="warning"
                    icon="heroicon-o-arrow-path"
                    wire:click="mountAction('resetDemoData')"
                >
                    Reset Dummy Data
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
