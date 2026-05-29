<?php

namespace App\Filament\Resources\EmployeeFamilies\Tables;

use App\Filament\Exports\EmployeeFamilyExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                TextColumn::make('name')
                    ->label('Nama Keluarga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('relationship')
                    ->label('Hubungan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('is_dependent')
                    ->label('Tanggungan')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('relationship')
                    ->options([
                        'Pasangan' => 'Pasangan',
                        'Anak' => 'Anak',
                        'Orang Tua' => 'Orang Tua',
                        'Saudara' => 'Saudara',
                    ])
                    ->label('Hubungan'),
            ])
            ->defaultSort('name')
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
