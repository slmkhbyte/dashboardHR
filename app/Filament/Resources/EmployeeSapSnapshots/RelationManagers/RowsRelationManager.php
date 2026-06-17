<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RowsRelationManager extends RelationManager
{
    protected static string $relationship = 'rows';

    protected static ?string $title = 'Snapshot Data SAP';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nik_karyawan')
                    ->label('NIK Karyawan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('work_unit')
                    ->label('Work Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employment_status')
                    ->label('Status Karyawan')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('employee_grade')
                    ->label('Golongan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('lvl_bod')
                    ->label('LVL BOD')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hire_date')
                    ->label('Tanggal Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(),
            ])
            ->defaultSort('full_name')
            ->paginated([10, 25, 50, 100]);
    }
}
