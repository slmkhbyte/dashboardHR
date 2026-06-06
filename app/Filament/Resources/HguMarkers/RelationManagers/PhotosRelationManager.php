<?php

namespace App\Filament\Resources\HguMarkers\RelationManagers;

use App\Models\HguMarkerPhoto;
use App\Support\HguMarkerPhotoStorage;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Foto Patok';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo_upload')
                    ->label('Foto')
                    ->image()
                    ->disk(HguMarkerPhotoStorage::tempDisk())
                    ->directory(HguMarkerPhotoStorage::tempDirectory())
                    ->visibility('private')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => HguMarkerPhotoStorage::generateStoredFilename($file),
                    )
                    ->imageEditor()
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->imageResizeUpscale(false)
                    ->fetchFileInformation(false)
                    ->maxSize(4096)
                    ->required(fn (string $operation): bool => $operation === 'create')
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
                ImageColumn::make('image_url')
                    ->label('Foto')
                    ->getStateUsing(fn (HguMarkerPhoto $record): ?string => $record->image_url)
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
                        $path = $data['photo_upload'] ?? null;

                        if (blank($path)) {
                            unset($data['photo_upload']);

                            return $data;
                        }

                        try {
                            $data = array_merge($data, HguMarkerPhotoStorage::buildDatabasePayload($path));
                        } finally {
                            HguMarkerPhotoStorage::deleteTempFile($path);
                        }

                        $data['uploaded_by'] = auth()->id();
                        unset($data['photo_upload']);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $path = $data['photo_upload'] ?? null;

                        if (filled($path)) {
                            try {
                                $data = array_merge($data, HguMarkerPhotoStorage::buildDatabasePayload($path));
                            } finally {
                                HguMarkerPhotoStorage::deleteTempFile($path);
                            }
                        }

                        unset($data['photo_upload']);

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
