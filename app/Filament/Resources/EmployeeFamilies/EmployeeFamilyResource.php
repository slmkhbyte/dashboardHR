<?php

namespace App\Filament\Resources\EmployeeFamilies;
use App\Filament\Resources\EmployeeFamilies\Pages\CreateEmployeeFamily;
use App\Filament\Resources\EmployeeFamilies\Pages\EditEmployeeFamily;
use App\Filament\Resources\EmployeeFamilies\Pages\ListEmployeeFamilyImportFailedRows;
use App\Filament\Resources\EmployeeFamilies\Pages\ListEmployeeFamilyImports;
use App\Filament\Resources\EmployeeFamilies\Pages\ListEmployeeFamilies;
use App\Filament\Resources\EmployeeFamilies\Schemas\EmployeeFamilyForm;
use App\Filament\Resources\EmployeeFamilies\Tables\EmployeeFamiliesTable;
use App\Models\EmployeeFamily;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeFamilyResource extends Resource
{
    protected static ?string $model = EmployeeFamily::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional HR';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Keluarga Karyawan';

    protected static ?string $pluralModelLabel = 'Keluarga Karyawan';

    public static function form(Schema $schema): Schema
    {
        return EmployeeFamilyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeFamiliesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeFamilies::route('/'),
            'create' => CreateEmployeeFamily::route('/create'),
            'edit' => EditEmployeeFamily::route('/{record}/edit'),
            'imports' => ListEmployeeFamilyImports::route('/imports'),
            'failed-import-rows' => ListEmployeeFamilyImportFailedRows::route('/imports/{import}/failed-rows'),
        ];
    }
}
