<?php

namespace App\Filament\Resources\EmploymentStatuses\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'Daftar Karyawan';

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Aktif')
                ->badge((string) $this->getOwnerRecord()->employees()->active()->count())
                ->modifyQueryUsing(fn ($query) => $query->active()),
            'all' => Tab::make('Semua')
                ->badge((string) $this->getOwnerRecord()->employees()->count()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
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
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('hire_date')
                    ->label('Tanggal Bergabung')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('full_name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()
                    ->url(fn (Employee $record): string => EmployeeResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
