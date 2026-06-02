<?php

namespace App\Filament\Resources\HguMarkers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Foto Patok';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('hgu-marker-photos')
                    ->visibility('public')
                    ->imageEditor()
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->imageResizeUpscale(false)
                    ->fetchFileInformation(false)
                    ->maxSize(4096)
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('uploaded_at')
                    ->label('Tanggal Upload')
                    ->default(fn () => now())
                    ->seconds(false),
                Textarea::make('caption')
                    ->label('Catatan Foto')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->height(72)
                    ->square(),
                TextColumn::make('caption')
                    ->label('Catatan')
                    ->wrap()
                    ->limit(80),
                TextColumn::make('uploaded_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('uploadedBy.name')
                    ->label('Uploader')
                    ->placeholder('System'),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
