<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Perbandingan Karyawan Lokal dan SAP per Afdeling
        </x-slot>

        <x-slot name="description">
            Data SAP terbaru: {{ $this->getLatestSnapshotLabel() ?? 'Belum ada snapshot SAP' }}
        </x-slot>

        @php
            $rows = $this->getComparisonRows();
            $statusColumns = [
                'karpel_tetap' => 'Karpel-Tetap',
                'ktng' => 'KTNG',
                'pkwt' => 'PKWT',
                'total' => 'Total',
            ];
        @endphp

        <style>
            .sap-comparison {
                overflow: hidden;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                background: #ffffff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.08);
            }

            .sap-comparison-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                border-bottom: 1px solid #e5e7eb;
                background: #fafafa;
                padding: 0.75rem 1rem;
            }

            .sap-comparison-snapshot {
                color: #6b7280;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .sap-comparison-badges {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .sap-comparison-scroll {
                overflow-x: auto;
            }

            .sap-comparison-table {
                width: 100%;
                min-width: 1060px;
                border-collapse: separate;
                border-spacing: 0;
                table-layout: fixed;
                font-size: 0.875rem;
            }

            .sap-comparison-table thead th {
                border-bottom: 1px solid #e5e7eb;
                background: #f9fafb;
                color: #4b5563;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.03em;
                padding: 0.75rem;
                text-transform: uppercase;
            }

            .sap-comparison-table tbody td {
                border-bottom: 1px solid #eef2f7;
                color: #374151;
                padding: 0.75rem;
                vertical-align: middle;
            }

            .sap-comparison-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .sap-comparison-table tbody tr:hover td {
                background: #f9fafb;
            }

            .sap-comparison-band-even td {
                background: #ffffff;
            }

            .sap-comparison-band-odd td {
                background: #f8fafc;
            }

            .sap-comparison-band-even:hover td {
                background: #f9fafb;
            }

            .sap-comparison-band-odd:hover td {
                background: #f1f5f9;
            }

            .sap-comparison-left {
                text-align: left;
            }

            .sap-comparison-center {
                text-align: center;
            }

            .sap-comparison-divider {
                border-left: 1px solid #e5e7eb;
            }

            .sap-comparison-work-unit {
                border-right: 1px solid #eef2f7;
                font-weight: 600;
                color: #111827 !important;
            }

            .sap-comparison-work-unit-inner {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .sap-comparison-work-unit-code {
                display: inline-flex;
                width: 2rem;
                height: 2rem;
                flex: 0 0 auto;
                align-items: center;
                justify-content: center;
                border: 1px solid #f59e0b;
                border-radius: 0.5rem;
                background: #fffbeb;
                color: #92400e;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .sap-comparison-position {
                padding: 0.75rem;
            }

            .sap-comparison-count {
                font-variant-numeric: tabular-nums;
                text-align: center;
            }

            .sap-comparison-total {
                background: #f3f4f6 !important;
                color: #111827 !important;
                font-weight: 700;
            }

            .sap-comparison-band-odd .sap-comparison-total {
                background: #e8edf4 !important;
            }

            .sap-comparison-empty {
                display: flex;
                min-height: 10rem;
                align-items: center;
                justify-content: center;
                border: 1px dashed #d1d5db;
                border-radius: 0.75rem;
                background: #f9fafb;
                padding: 2.5rem 1.5rem;
                text-align: center;
            }

            .sap-comparison-empty > div {
                display: grid;
                justify-items: center;
                gap: 0.5rem;
            }

            .sap-comparison-empty-icon {
                display: inline-flex;
                width: 2.5rem;
                height: 2.5rem;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
                background: #f3f4f6;
                color: #6b7280;
            }

            .sap-comparison-empty-icon svg {
                width: 1.25rem;
                height: 1.25rem;
            }

            .sap-comparison-empty-title {
                margin: 0;
                color: #111827;
                font-size: 0.875rem;
                font-weight: 600;
            }

            .sap-comparison-empty-body {
                margin: 0.5rem 0 0;
                color: #6b7280;
                font-size: 0.875rem;
            }

            @media (prefers-color-scheme: dark) {
                .sap-comparison {
                    border-color: rgba(255, 255, 255, 0.1);
                    background: #111827;
                    box-shadow: none;
                }

                .sap-comparison-toolbar,
                .sap-comparison-table thead th {
                    border-color: rgba(255, 255, 255, 0.1);
                    background: rgba(255, 255, 255, 0.04);
                }

                .sap-comparison-table thead th,
                .sap-comparison-snapshot {
                    color: #d1d5db;
                }

                .sap-comparison-table tbody td {
                    border-color: rgba(255, 255, 255, 0.08);
                    color: #e5e7eb;
                }

                .sap-comparison-table tbody tr:hover td {
                    background: rgba(255, 255, 255, 0.04);
                }

                .sap-comparison-band-even td {
                    background: #111827;
                }

                .sap-comparison-band-odd td {
                    background: #162033;
                }

                .sap-comparison-band-even:hover td {
                    background: rgba(255, 255, 255, 0.04);
                }

                .sap-comparison-band-odd:hover td {
                    background: #1b2940;
                }

                .sap-comparison-divider,
                .sap-comparison-work-unit {
                    border-color: rgba(255, 255, 255, 0.1);
                }

                .sap-comparison-work-unit {
                    color: #ffffff !important;
                }

                .sap-comparison-work-unit-code {
                    border-color: rgba(245, 158, 11, 0.45);
                    background: rgba(245, 158, 11, 0.12);
                    color: #fbbf24;
                }

                .sap-comparison-total {
                    background: rgba(255, 255, 255, 0.06) !important;
                    color: #ffffff !important;
                }

                .sap-comparison-band-odd .sap-comparison-total {
                    background: rgba(255, 255, 255, 0.09) !important;
                }

                .sap-comparison-empty {
                    border-color: rgba(255, 255, 255, 0.16);
                    background: rgba(255, 255, 255, 0.04);
                }

                .sap-comparison-empty-icon {
                    background: rgba(255, 255, 255, 0.06);
                    color: #9ca3af;
                }

                .sap-comparison-empty-title {
                    color: #ffffff;
                }

                .sap-comparison-empty-body {
                    color: #9ca3af;
                }
            }
        </style>

        @if (count($rows) === 0)
            <div class="sap-comparison-empty">
                <div>
                    <div class="sap-comparison-empty-icon">
                        <x-filament::icon
                            icon="heroicon-o-table-cells"
                        />
                    </div>

                    <p class="sap-comparison-empty-title">
                        Belum ada data afdeling
                    </p>

                    <p class="sap-comparison-empty-body">
                        Data untuk jabatan PEMANEN atau PEMELIHARAAN belum tersedia.
                    </p>
                </div>
            </div>
        @else
            <div class="sap-comparison">
                <div class="sap-comparison-toolbar">
                    <div class="sap-comparison-badges">
                        <x-filament::badge color="gray">
                            {{ count($rows) }} Afdeling
                        </x-filament::badge>

                        <x-filament::badge color="primary">
                            PEMANEN
                        </x-filament::badge>

                        <x-filament::badge color="warning">
                            PEMELIHARAAN
                        </x-filament::badge>
                    </div>

                    <div class="sap-comparison-snapshot">
                        Snapshot SAP: {{ $this->getLatestSnapshotLabel() ?? '-' }}
                    </div>
                </div>

                <div class="sap-comparison-scroll">
                    <table class="sap-comparison-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sap-comparison-left" style="width: 11rem;">Afdeling</th>
                                <th rowspan="2" class="sap-comparison-left" style="width: 10rem;">Jabatan</th>
                                <th colspan="4" class="sap-comparison-center sap-comparison-divider">
                                    Data Lokal
                                </th>
                                <th colspan="4" class="sap-comparison-center sap-comparison-divider">
                                    Data SAP
                                </th>
                            </tr>
                            <tr>
                                @foreach ($statusColumns as $statusKey => $label)
                                    <th class="sap-comparison-center sap-comparison-divider">
                                        {{ $label }}
                                    </th>
                                @endforeach

                                @foreach ($statusColumns as $statusKey => $label)
                                    <th class="sap-comparison-center sap-comparison-divider">
                                        {{ $label }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rows as $row)
                                @foreach ($row['positions'] as $position => $counts)
                                    <tr class="{{ $loop->parent->even ? 'sap-comparison-band-even' : 'sap-comparison-band-odd' }}">
                                        @if ($loop->first)
                                            <td rowspan="{{ count($row['positions']) }}" class="sap-comparison-work-unit">
                                                <div class="sap-comparison-work-unit-inner">
                                                    <div class="sap-comparison-work-unit-code">
                                                        {{ str($row['work_unit'])->replace('AFDELING ', '') }}
                                                    </div>

                                                    <span>
                                                        {{ $row['work_unit'] }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif

                                        <td class="sap-comparison-position">
                                            <x-filament::badge :color="$position === 'PEMANEN' ? 'primary' : 'warning'">
                                                {{ $position }}
                                            </x-filament::badge>
                                        </td>

                                        @foreach ($statusColumns as $statusKey => $label)
                                            <td class="@class([
                                                'sap-comparison-count sap-comparison-divider',
                                                'sap-comparison-total' => $statusKey === 'total',
                                            ])">
                                                {{ number_format($counts['local'][$statusKey]) }}
                                            </td>
                                        @endforeach

                                        @foreach ($statusColumns as $statusKey => $label)
                                            <td class="@class([
                                                'sap-comparison-count sap-comparison-divider',
                                                'sap-comparison-total' => $statusKey === 'total',
                                            ])">
                                                {{ number_format($counts['sap'][$statusKey]) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
