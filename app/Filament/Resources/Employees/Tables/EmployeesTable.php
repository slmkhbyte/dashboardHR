<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Exports\EmployeeExporter;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->sortable()
                    ->color(fn (Employee $record): string => $record->has_import_warnings ? 'danger' : 'primary'),
                TextColumn::make('nik_karyawan')
                    ->label('NIK Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('work_unit')
                    ->label('Work Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position.name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employmentStatus.name')
                    ->label('Status Karyawan')
                    ->badge()
                    ->color(fn ($record): string => $record->employmentStatus?->color ?? 'gray')
                    ->sortable(),
                TextColumn::make('employee_grade')
                    ->label('Golongan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('religion')
                    ->label('Agama')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('dependent_code')
                    ->label('Tanggungan')
                    ->state(fn (Employee $record): ?string => $record->dependent_code)
                    ->toggleable(),
                TextColumn::make('lvl_bod')
                    ->label('LVL BOD')
                    ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : '-' . $state)
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('hire_date')
                    ->label('Tanggal Bergabung')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('award_20_years')
                    ->label('Penghargaan 20 Tahun')
                    ->state(fn (Employee $record): ?string => $record->awardDateForYears(20)?->translatedFormat('d M Y'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->relationship('position', 'name')
                    ->label('Jabatan'),
                SelectFilter::make('employment_status_id')
                    ->relationship('employmentStatus', 'name')
                    ->label('Status Karyawan'),
                SelectFilter::make('work_unit')
                    ->label('Work Unit')
                    ->options(fn (): array => Employee::query()
                        ->whereNotNull('work_unit')
                        ->where('work_unit', '!=', '')
                        ->distinct()
                        ->orderBy('work_unit')
                        ->pluck('work_unit', 'work_unit')
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->recordClasses(fn (Employee $record): array | string => $record->has_import_warnings ? ['bg-red-50', 'dark:bg-red-950'] : '')
            ->defaultSort('full_name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor CSV')
                    ->exporter(EmployeeExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
