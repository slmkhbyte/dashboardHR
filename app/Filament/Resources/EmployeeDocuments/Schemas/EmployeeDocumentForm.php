<?php

namespace App\Filament\Resources\EmployeeDocuments\Schemas;

use App\Models\EmployeeDocument;
use App\Support\EmployeeDocumentImageStorage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EmployeeDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumen Karyawan')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('document_name')
                            ->label('Nama Dokumen')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('document_type')
                            ->label('Jenis Dokumen')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->maxLength(255),
                        DatePicker::make('issued_at')
                            ->label('Tanggal Terbit'),
                        DatePicker::make('expires_at')
                            ->label('Tanggal Kedaluwarsa'),
                        Select::make('status')
                            ->label('Status Dokumen')
                            ->required()
                            ->default('complete')
                            ->options([
                                'complete' => 'Complete',
                                'expiring' => 'Expiring',
                                'expired' => 'Expired',
                            ]),
                        FileUpload::make('image_upload')
                            ->label('Gambar Dokumen')
                            ->image()
                            ->disk(EmployeeDocumentImageStorage::tempDisk())
                            ->directory(EmployeeDocumentImageStorage::tempDirectory())
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => EmployeeDocumentImageStorage::generateStoredFilename($file),
                            )
                            ->imageEditor()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1600')
                            ->imageResizeTargetHeight('1600')
                            ->imageResizeUpscale(false)
                            ->fetchFileInformation(false)
                            ->maxSize(4096)
                            ->helperText('Opsional. Hanya mendukung file gambar seperti JPG, PNG, atau WebP.')
                            ->columnSpanFull(),
                        Placeholder::make('current_image_preview')
                            ->label('Preview Gambar Saat Ini')
                            ->content(function (?EmployeeDocument $record): HtmlString {
                                if (blank($record?->image_url)) {
                                    return new HtmlString('Belum ada gambar diupload.');
                                }

                                $previewUrl = e($record->image_url);
                                $downloadUrl = e($record->image_download_url ?? $record->image_url);
                                $filename = e($record->image_original_filename ?? 'gambar-dokumen');

                                return new HtmlString(<<<HTML
                                    <div class="space-y-3">
                                        <img src="{$previewUrl}" alt="{$filename}" class="max-h-56 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10" />
                                        <div class="flex flex-wrap gap-3 text-sm">
                                            <a href="{$previewUrl}" target="_blank" class="text-primary-600 underline">Buka gambar</a>
                                            <a href="{$downloadUrl}" class="text-gray-700 underline dark:text-gray-200">Unduh gambar</a>
                                        </div>
                                    </div>
                                HTML);
                            })
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
