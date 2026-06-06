<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Models\Position;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jabatan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Jabatan')
                            ->required()
                            ->disabled(fn (?Position $record): bool => $record?->isDefault() ?? false)
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode Jabatan')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
