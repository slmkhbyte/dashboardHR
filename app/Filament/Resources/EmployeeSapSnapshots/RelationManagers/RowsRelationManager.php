<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RowsRelationManager extends RelationManager
{
    protected static string $relationship = 'rows';

    protected static ?string $title = 'Snapshot SAP';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('work_unit')
                    ->label('Work Unit')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('lvl_bod')
                    ->label('LVL BOD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('employee_grade')
                    ->label('Golongan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('employment_status')
                    ->label('Status')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department')
                    ->label('Department')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('division')
                    ->label('Division')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('superior')
                    ->label('Superior')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }
}
