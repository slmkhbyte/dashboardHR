<?php

namespace App\Filament\Resources\EmploymentStatuses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmploymentStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Status Kerja')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Status')
                            ->required()
                            ->maxLength(255),
                        Select::make('color')
                            ->label('Warna Badge')
                            ->required()
                            ->default('info')
                            ->options([
                                'gray' => 'Gray',
                                'info' => 'Info',
                                'success' => 'Success',
                                'warning' => 'Warning',
                                'danger' => 'Danger',
                            ]),
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
