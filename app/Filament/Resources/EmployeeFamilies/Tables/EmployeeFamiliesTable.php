<?php

namespace App\Filament\Resources\EmployeeFamilies\Tables;

use App\Filament\Exports\EmployeeFamilyExporter;
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
use Illuminate\Database\Eloquent\Builder;

class EmployeeFamiliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nama Keluarga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('relationship')
                    ->label('Hubungan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('birth_place')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('last_education')
                    ->label('Pendidikan')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('religion')
                    ->label('Agama')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ethnicity')
                    ->label('Suku')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_dependent')
                    ->label('Tanggungan')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('relationship')
                    ->options([
                        'Pasangan' => 'Pasangan',
                        'Istri' => 'Istri',
                        'Suami' => 'Suami',
                        'Anak' => 'Anak',
                        'Orang Tua' => 'Orang Tua',
                        'Saudara' => 'Saudara',
                    ])
                    ->label('Hubungan'),
            ])
            ->defaultSort(
                fn (Builder $query, string $direction): Builder => $query
                    ->orderBy(
                        Employee::query()
                            ->select('full_name')
                            ->whereColumn('employees.id', 'employee_families.employee_id')
                            ->limit(1),
                        $direction,
                    )
                    ->orderBy('name'),
            )
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor CSV')
                    ->exporter(EmployeeFamilyExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
