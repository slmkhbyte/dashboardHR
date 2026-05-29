<?php

namespace App\Filament\Resources\EmployeeDocuments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
