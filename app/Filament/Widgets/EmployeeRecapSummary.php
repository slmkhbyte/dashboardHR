<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class EmployeeRecapSummary extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.employee-recap-summary';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -8;

    protected static bool $isDiscovered = false;

    /**
     * @return array<int, array{key: string, label: string, value: string, description: string, icon: string, color: string}>
     */
    public function getCards(): array
    {
        $totalEmployees = Employee::query()->count();

        return [
            [
                'key' => 'total',
                'label' => 'Total Karyawan',
                'value' => number_format($totalEmployees, 0, ',', '.'),
                'description' => 'Klik untuk melihat ringkasan data',
                'icon' => 'heroicon-m-users',
                'color' => 'primary',
            ],
            [
                'key' => 'positions',
                'label' => 'Jabatan',
                'value' => number_format($this->getPositionCounts()->count(), 0, ',', '.'),
                'description' => 'Kategori jabatan tercatat',
                'icon' => 'heroicon-m-briefcase',
                'color' => 'info',
            ],
            [
                'key' => 'statuses',
                'label' => 'Status Karyawan',
                'value' => number_format($this->getEmploymentStatusCounts()->count(), 0, ',', '.'),
                'description' => 'Kategori status tercatat',
                'icon' => 'heroicon-m-identification',
                'color' => 'success',
            ],
            [
                'key' => 'levels',
                'label' => 'Level BOD',
                'value' => number_format($this->getLevelBodCounts()->count(), 0, ',', '.'),
                'description' => 'Level yang memiliki karyawan',
                'icon' => 'heroicon-m-squares-2x2',
                'color' => 'warning',
            ],
            [
                'key' => 'work_units',
                'label' => 'Work Unit',
                'value' => number_format($this->getWorkUnitCounts()->count(), 0, ',', '.'),
                'description' => 'Unit kerja tercatat',
                'icon' => 'heroicon-m-building-office-2',
                'color' => 'gray',
            ],
        ];
    }

    public function showRecapAction(): Action
    {
        return Action::make('showRecap')
            ->modalHeading(fn (array $arguments): string => $this->getModalHeading($arguments['type'] ?? 'total'))
            ->modalContent(fn (array $arguments) => view('filament.widgets.employee-recap-detail', [
                'items' => $this->getModalItems($arguments['type'] ?? 'total'),
                'summary' => $this->getModalSummary($arguments['type'] ?? 'total'),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalWidth('3xl');
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    public function getPositionCounts(): Collection
    {
        return Employee::query()
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->selectRaw("COALESCE(positions.name, 'Belum Diisi') as label, count(*) as aggregate")
            ->groupByRaw('positions.name')
            ->orderByDesc('aggregate')
            ->orderByRaw("COALESCE(positions.name, 'Belum Diisi')")
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'count' => (int) $row->aggregate,
            ]);
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    public function getEmploymentStatusCounts(): Collection
    {
        return Employee::query()
            ->leftJoin('employment_statuses', 'employment_statuses.id', '=', 'employees.employment_status_id')
            ->selectRaw("COALESCE(employment_statuses.name, 'Belum Diisi') as label, count(*) as aggregate")
            ->groupByRaw('employment_statuses.name')
            ->orderByDesc('aggregate')
            ->orderByRaw("COALESCE(employment_statuses.name, 'Belum Diisi')")
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'count' => (int) $row->aggregate,
            ]);
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    public function getLevelBodCounts(): Collection
    {
        return Employee::query()
            ->selectRaw("COALESCE(CAST(lvl_bod AS TEXT), 'Belum Diisi') as label, count(*) as aggregate")
            ->groupBy('lvl_bod')
            ->orderByRaw('CASE WHEN lvl_bod IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lvl_bod')
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label === 'Belum Diisi' ? $row->label : 'Level ' . $row->label,
                'count' => (int) $row->aggregate,
            ]);
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    public function getWorkUnitCounts(): Collection
    {
        return Employee::query()
            ->selectRaw("COALESCE(NULLIF(work_unit, ''), 'Belum Diisi') as label, count(*) as aggregate")
            ->groupByRaw("NULLIF(work_unit, '')")
            ->orderByDesc('aggregate')
            ->orderByRaw("COALESCE(NULLIF(work_unit, ''), 'Belum Diisi')")
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'count' => (int) $row->aggregate,
            ]);
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    protected function getGenderCounts(): Collection
    {
        return Employee::query()
            ->selectRaw("COALESCE(NULLIF(gender, ''), 'Belum Diisi') as label, count(*) as aggregate")
            ->groupByRaw("NULLIF(gender, '')")
            ->orderByDesc('aggregate')
            ->orderByRaw("COALESCE(NULLIF(gender, ''), 'Belum Diisi')")
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'count' => (int) $row->aggregate,
            ]);
    }

    protected function getModalHeading(string $type): string
    {
        return match ($type) {
            'positions' => 'Detail Karyawan per Jabatan',
            'statuses' => 'Detail Karyawan per Status',
            'levels' => 'Detail Karyawan per Level BOD',
            'work_units' => 'Detail Karyawan per Work Unit',
            default => 'Detail Total Karyawan',
        };
    }

    /**
     * @return Collection<int, array{label: string, count: int}>
     */
    protected function getModalItems(string $type): Collection
    {
        return match ($type) {
            'positions' => $this->getPositionCounts(),
            'statuses' => $this->getEmploymentStatusCounts(),
            'levels' => $this->getLevelBodCounts(),
            'work_units' => $this->getWorkUnitCounts(),
            default => collect([
                [
                    'label' => 'Total karyawan',
                    'count' => Employee::query()->count(),
                ],
                [
                    'label' => 'Data belum lengkap',
                    'count' => Employee::query()
                        ->where(function ($query): void {
                            $query
                                ->whereNull('full_name')
                                ->orWhereNull('position_id')
                                ->orWhereNull('employment_status_id')
                                ->orWhereNull('work_unit')
                                ->orWhereNull('lvl_bod')
                                ->orWhereNull('hire_date');
                        })
                        ->count(),
                ],
                [
                    'label' => 'Tanpa data keluarga',
                    'count' => Employee::query()->doesntHave('families')->count(),
                ],
            ])->merge($this->getGenderCounts()->map(fn (array $item): array => [
                'label' => 'Gender: ' . $item['label'],
                'count' => $item['count'],
            ]))->values(),
        };
    }

    /**
     * @return array{total: int, categories: int}
     */
    protected function getModalSummary(string $type): array
    {
        $items = $this->getModalItems($type);

        return [
            'total' => $type === 'total' ? Employee::query()->count() : $items->sum('count'),
            'categories' => $items->count(),
        ];
    }
}
