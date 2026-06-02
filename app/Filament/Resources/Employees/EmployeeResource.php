<?php

namespace App\Filament\Resources\Employees;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployeeImportFailedRows;
use App\Filament\Resources\Employees\Pages\ListEmployeeImports;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\RelationManagers\HistoriesRelationManager;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional HR';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Karyawan';

    protected static ?string $pluralModelLabel = 'Karyawan';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
            'imports' => ListEmployeeImports::route('/imports'),
            'failed-import-rows' => ListEmployeeImportFailedRows::route('/imports/{import}/failed-rows'),
        ];
    }
}
