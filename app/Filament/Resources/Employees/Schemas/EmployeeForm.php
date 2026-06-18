<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Karyawan')
                    ->schema([
                        TextInput::make('nik_sap')
                            ->label('NIK SAP')
                            ->required()
                            ->length(8)
                            ->rule('digits:8')
                            ->unique(ignoreRecord: true),
                        TextInput::make('nik_karyawan')
                            ->label('NIK Karyawan')
                            ->placeholder('000.0194.0573.0337 atau 000019405730337')
                            ->required()
                            ->maxLength(18)
                            ->rule('regex:/^(?:\d{3}\.\d{4}\.\d{4}\.\d{4}|\d{16})$/')
                            ->unique(ignoreRecord: true),
                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(50),
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ]),
                        TextInput::make('religion')
                            ->label('Agama')
                            ->maxLength(255),
                        TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Tanggal Lahir'),
                        TextInput::make('last_education')
                            ->label('Pendidikan Terakhir')
                            ->maxLength(255),
                        DatePicker::make('hire_date')
                            ->label('Tanggal Bergabung')
                            ->live()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Struktur Organisasi')
                    ->schema([
                        Select::make('position_id')
                            ->label('Jabatan')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employment_status_id')
                            ->label('Status Karyawan')
                            ->relationship('employmentStatus', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('employee_grade')
                            ->label('Golongan Karyawan')
                            ->placeholder('IB/13, IIID/ 06, atau format lain')
                            ->maxLength(50),
                        TextInput::make('work_unit')
                            ->label('Work Unit')
                            ->placeholder('AFDELING I')
                            ->datalist(fn (): array => Employee::query()
                                ->whereNotNull('work_unit')
                                ->where('work_unit', '!=', '')
                                ->distinct()
                                ->orderBy('work_unit')
                                ->pluck('work_unit')
                                ->all())
                            ->maxLength(255),
                        TextInput::make('lvl_bod')
                            ->label('LVL BOD')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99),
                        Select::make('marital_status')
                            ->label('Status Tanggungan')
                            ->options([
                                'TK' => 'TK - Tidak kawin',
                                'K' => 'K - Kawin',
                            ]),
                        TextInput::make('dependent_count')
                            ->label('Jumlah Tanggungan Anak')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99)
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Penghargaan Masa Kerja')
                    ->schema([
                        Placeholder::make('award_dates')
                            ->label('Jadwal Penghargaan')
                            ->content(function ($get): HtmlString {
                                $hireDate = $get('hire_date');

                                if (blank($hireDate)) {
                                    return new HtmlString('Isi tanggal bergabung dulu.');
                                }

                                $date = Carbon::parse($hireDate);
                                $items = collect([20, 25, 30, 35])
                                    ->map(function (int $years) use ($date): string {
                                        $awardDate = $date->copy()
                                            ->addYears($years)
                                            ->addMonth()
                                            ->startOfMonth()
                                            ->translatedFormat('d M Y');

                                        return "<li>{$years} tahun: {$awardDate}</li>";
                                    })
                                    ->implode('');

                                return new HtmlString("<ul class=\"list-disc space-y-1 ps-5 text-sm\">{$items}</ul>");
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('Informasi Tambahan')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
