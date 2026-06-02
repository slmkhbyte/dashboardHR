<?php

namespace App\Filament\Resources\HguMarkers;

use App\Filament\Resources\HguMarkers\Pages\CreateHguMarker;
use App\Filament\Resources\HguMarkers\Pages\EditHguMarker;
use App\Filament\Resources\HguMarkers\Pages\ListHguMarkerImportFailedRows;
use App\Filament\Resources\HguMarkers\Pages\ListHguMarkerImports;
use App\Filament\Resources\HguMarkers\Pages\ListHguMarkers;
use App\Filament\Resources\HguMarkers\RelationManagers\HistoriesRelationManager;
use App\Filament\Resources\HguMarkers\RelationManagers\MovesRelationManager;
use App\Filament\Resources\HguMarkers\RelationManagers\PhotosRelationManager;
use App\Filament\Resources\HguMarkers\Schemas\HguMarkerForm;
use App\Filament\Resources\HguMarkers\Tables\HguMarkersTable;
use App\Models\HguMarker;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HguMarkerResource extends Resource
{
    protected static ?string $model = HguMarker::class;

    protected static ?string $recordTitleAttribute = 'marker_number';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring HGU';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Data Patok HGU';

    protected static ?string $pluralModelLabel = 'Data Patok HGU';

    public static function form(Schema $schema): Schema
    {
        return HguMarkerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HguMarkersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhotosRelationManager::class,
            MovesRelationManager::class,
            HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHguMarkers::route('/'),
            'create' => CreateHguMarker::route('/create'),
            'edit' => EditHguMarker::route('/{record}/edit'),
            'imports' => ListHguMarkerImports::route('/imports'),
            'failed-import-rows' => ListHguMarkerImportFailedRows::route('/imports/{import}/failed-rows'),
        ];
    }
}
