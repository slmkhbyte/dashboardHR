<?php

namespace App\Filament\Resources\HguMarkers\Pages;

use App\Filament\Imports\HguMarkerImporter;
use App\Filament\Resources\HguMarkers\HguMarkerResource;
use App\Filament\Resources\HguMarkers\Widgets\HguMarkerConditionOverview;
use App\Support\HguMarkerPhotoBatchImporter;
use App\Support\HguMarkerPhotoStorage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListHguMarkers extends ListRecords
{
    protected static string $resource = HguMarkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Impor CSV')
                ->importer(HguMarkerImporter::class)
                ->successRedirectUrl(HguMarkerResource::getUrl('imports'))
                ->slideOver(),
            Action::make('batchUploadPhotos')
                ->label('Upload Foto Batch')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->schema([
                    FileUpload::make('photos')
                        ->label('Foto Patok')
                        ->multiple()
                        ->image()
                        ->disk(HguMarkerPhotoStorage::tempDisk())
                        ->directory(HguMarkerPhotoStorage::tempDirectory())
                        ->visibility('private')
                        ->getUploadedFileNameForStorageUsing(
                            fn (TemporaryUploadedFile $file): string => HguMarkerPhotoStorage::generateStoredFilename($file),
                        )
                        ->helperText('Nama file harus sama dengan nomor patok. Contoh: 1.jpg atau 001.jpg akan dicocokkan ke patok 1.')
                        ->maxSize(4096)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->modalWidth('2xl')
                ->action(function (array $data): void {
                    $result = app(HguMarkerPhotoBatchImporter::class)->import(
                        paths: array_values($data['photos'] ?? []),
                        disk: HguMarkerPhotoStorage::tempDisk(),
                        uploadedBy: auth()->id(),
                    );

                    $body = "{$result['created']} foto berhasil dihubungkan ke patok.";

                    if (count($result['skipped']) > 0) {
                        $body .= ' ' . count($result['skipped']) . ' file dilewati karena nomor patok tidak ditemukan: '
                            . collect($result['skipped'])->take(5)->implode(', ');
                    }

                    Notification::make()
                        ->title('Upload foto batch selesai')
                        ->body($body)
                        ->color(count($result['skipped']) > 0 ? 'warning' : 'success')
                        ->send();
                }),
            Action::make('importHistory')
                ->label('Riwayat Import')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(HguMarkerResource::getUrl('imports')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HguMarkerConditionOverview::class,
        ];
    }
}
