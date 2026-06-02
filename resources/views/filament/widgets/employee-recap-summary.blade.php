@php
    $toneClasses = [
        'primary' => 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-300 dark:ring-primary-400/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/20',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        'gray' => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-white/10 dark:text-gray-200 dark:ring-white/10',
    ];
@endphp

<x-filament-widgets::widget>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
        @foreach ($this->getCards() as $card)
            <button
                type="button"
                wire:click="mountAction('showRecap', { type: @js($card['key']) })"
                class="group rounded-xl bg-white p-5 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-900 dark:ring-white/10"
            >
                <div class="flex items-start justify-between gap-4">
                    <span class="rounded-lg p-2.5 ring-1 {{ $toneClasses[$card['color']] ?? $toneClasses['gray'] }}">
                        <x-filament::icon
                            :icon="$card['icon']"
                            class="h-5 w-5"
                        />
                    </span>

                    <x-filament::icon
                        icon="heroicon-m-arrow-up-right"
                        class="h-4 w-4 text-gray-400 transition group-hover:text-primary-500"
                    />
                </div>

                <div class="mt-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $card['label'] }}
                    </p>

                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $card['value'] }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ $card['description'] }}
                    </p>
                </div>
            </button>
        @endforeach
    </div>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
