<?php

namespace App\Filament\Widgets;

use App\Support\ClearDemoDataService;
use App\Support\ResetDemoDataService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Throwable;

class DemoActionsCard extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected static ?int $sort = 100;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.demo-actions-card';

    protected int|string|array $columnSpan = 'full';

    public function clearDataAction(): Action
    {
        return Action::make('clearData')
            ->label('Kosongkan Data')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('Ya, kosongkan data')
            ->action(function (ClearDemoDataService $clearDemoDataService): void {
                $this->runClearDataAction($clearDemoDataService, includeMasterHr: false);
            });
    }

    public function clearDataWithMasterHrAction(): Action
    {
        return Action::make('clearDataWithMasterHr')
            ->label('Kosongkan Data + Master HR')
            ->color('danger')
            ->icon('heroicon-o-exclamation-triangle')
            ->requiresConfirmation()
            ->modalHeading('Kosongkan data demo dan master HR?')
            ->modalDescription('Aksi ini akan menghapus seluruh data bisnis HR, data HGU, riwayat import/export demo, serta seluruh master Jabatan dan Status Kerja non-default. Dua default sistem akan tetap dipertahankan.')
            ->modalSubmitActionLabel('Ya, kosongkan data + master HR')
            ->action(function (ClearDemoDataService $clearDemoDataService): void {
                $this->runClearDataAction($clearDemoDataService, includeMasterHr: true);
            });
    }

    public function resetDemoDataAction(): Action
    {
        return Action::make('resetDemoData')
            ->label('Reset Dummy Data')
            ->color('warning')
            ->icon('heroicon-o-arrow-path')
            ->requiresConfirmation()
            ->modalHeading('Reset seluruh dummy data?')
            ->modalDescription('Aksi ini akan menghapus seluruh data bisnis, membersihkan master HR non-default, lalu mengisi ulang baseline dummy demo HR dan HGU. Akun user akan tetap dipertahankan.')
            ->modalSubmitActionLabel('Ya, reset dummy data')
            ->action(function (ResetDemoDataService $resetDemoDataService): void {
                $this->runResetDemoDataAction($resetDemoDataService);
            });
    }

    private function runClearDataAction(ClearDemoDataService $clearDemoDataService, bool $includeMasterHr): void
    {
        try {
            $summary = $clearDemoDataService->execute(Filament::auth()->id(), includeMasterHr: $includeMasterHr);

            $masterHrCount = array_sum($summary['master_hr']);

            Notification::make()
                ->success()
                ->title($includeMasterHr ? 'Data demo dan master HR berhasil dikosongkan' : 'Data demo berhasil dikosongkan')
                ->body(sprintf(
                    'HR: %d, HGU: %d, Import/Export: %d, Master HR: %d, Total: %d data terhapus.',
                    array_sum($summary['hr']),
                    array_sum($summary['hgu']),
                    array_sum($summary['imports_exports']),
                    $masterHrCount,
                    $summary['total_deleted'],
                ))
                ->send();
        } catch (Throwable $throwable) {
            report($throwable);

            Notification::make()
                ->danger()
                ->title($includeMasterHr ? 'Gagal mengosongkan data dan master HR' : 'Gagal mengosongkan data')
                ->body('Terjadi kesalahan saat membersihkan data demo. Silakan cek log aplikasi.')
                ->send();
        }
    }

    private function runResetDemoDataAction(ResetDemoDataService $resetDemoDataService): void
    {
        try {
            $summary = $resetDemoDataService->execute(Filament::auth()->id());

            Notification::make()
                ->success()
                ->title('Dummy data berhasil direset')
                ->body(sprintf(
                    'Terhapus: %d data. Terisi ulang: %d karyawan, %d keluarga, %d dokumen, %d marker HGU.',
                    $summary['cleared']['total_deleted'],
                    $summary['seeded']['employees'],
                    $summary['seeded']['employee_families'],
                    $summary['seeded']['employee_documents'],
                    $summary['seeded']['hgu_markers'],
                ))
                ->send();
        } catch (Throwable $throwable) {
            report($throwable);

            Notification::make()
                ->danger()
                ->title('Gagal mereset dummy data')
                ->body('Terjadi kesalahan saat mereset dummy data. Silakan cek log aplikasi.')
                ->send();
        }
    }
}
