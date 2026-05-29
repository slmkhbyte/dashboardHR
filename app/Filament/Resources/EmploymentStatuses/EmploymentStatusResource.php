<?php

namespace App\Filament\Resources\EmploymentStatuses;
use App\Filament\Resources\EmploymentStatuses\Pages\CreateEmploymentStatus;
use App\Filament\Resources\EmploymentStatuses\Pages\EditEmploymentStatus;
use App\Filament\Resources\EmploymentStatuses\Pages\ListEmploymentStatuses;
use App\Filament\Resources\EmploymentStatuses\Schemas\EmploymentStatusForm;
use App\Filament\Resources\EmploymentStatuses\Tables\EmploymentStatusesTable;
use App\Models\EmploymentStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmploymentStatusResource extends Resource
{
    protected static ?string $model = EmploymentStatus::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string | \UnitEnum | null $navigationGroup = 'Master HR';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Status Kerja';

    protected static ?string $pluralModelLabel = 'Status Kerja';

    public static function form(Schema $schema): Schema
    {
        return EmploymentStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmploymentStatusesTable::configure($table);
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
            'index' => ListEmploymentStatuses::route('/'),
            'create' => CreateEmploymentStatus::route('/create'),
            'edit' => EditEmploymentStatus::route('/{record}/edit'),
        ];
    }
}
