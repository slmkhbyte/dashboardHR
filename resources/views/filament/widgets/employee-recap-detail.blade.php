<div class="space-y-4">
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Total
            </p>

            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">
                {{ number_format($summary['total'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Baris Detail
            </p>

            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">
                {{ number_format($summary['categories'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    @if ($items->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Belum ada data karyawan.
        </p>
    @else
        <div class="max-h-[28rem] overflow-y-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($items as $item)
                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3 dark:bg-gray-900">
                        <span class="min-w-0 truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $item['label'] }}
                        </span>

                        <span class="shrink-0 rounded-md bg-gray-100 px-2.5 py-1 text-sm font-semibold tabular-nums text-gray-900 dark:bg-white/10 dark:text-white">
                            {{ number_format($item['count'], 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
