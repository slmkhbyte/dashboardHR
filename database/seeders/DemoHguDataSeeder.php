<?php

namespace Database\Seeders;

use App\Models\HguMarker;
use Illuminate\Database\Seeder;

class DemoHguDataSeeder extends Seeder
{
    public function run(): void
    {
        HguMarker::withoutEvents(function (): void {
            collect($this->markers())->each(function (array $marker): void {
                HguMarker::query()->updateOrCreate(
                    ['marker_number' => $marker['marker_number']],
                    $marker,
                );
            });
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function markers(): array
    {
        return [
            [
                'marker_number' => '101',
                'afdeling' => 1,
                'utm_coordinates' => '49M 420553 9987669',
                'latitude' => -0.1115525,
                'longitude' => 110.2860453,
                'marker_type' => HguMarker::MARKER_TYPE_BETON,
                'condition' => HguMarker::CONDITION_BAIK,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(12)->toDateString(),
                'notes' => 'Patok demo area Afdeling I.',
            ],
            [
                'marker_number' => '102',
                'afdeling' => 2,
                'utm_coordinates' => '49M 420546 9987918',
                'latitude' => -0.1092989,
                'longitude' => 110.2859808,
                'marker_type' => HguMarker::MARKER_TYPE_PARALON,
                'condition' => HguMarker::CONDITION_RUSAK_RINGAN,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(21)->toDateString(),
                'notes' => 'Perlu inspeksi ringan.',
            ],
            [
                'marker_number' => '103',
                'afdeling' => 3,
                'utm_coordinates' => '49M 426056 9993887',
                'latitude' => -0.0666617,
                'longitude' => 110.3356697,
                'marker_type' => 'Pt.Kayu',
                'condition' => HguMarker::CONDITION_HILANG,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(33)->toDateString(),
                'notes' => 'Contoh marker type mentah untuk demo.',
            ],
            [
                'marker_number' => '218',
                'afdeling' => 6,
                'utm_coordinates' => '49M 428169.3784 9997859.991',
                'latitude' => -0.0193600,
                'longitude' => 110.3544900,
                'marker_type' => HguMarker::MARKER_TYPE_BETON,
                'condition' => HguMarker::CONDITION_BAIK,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(8)->toDateString(),
                'notes' => 'Data demo dari sampel impor HGU.',
            ],
            [
                'marker_number' => '219',
                'afdeling' => 7,
                'utm_coordinates' => '49M 423893.8246 9997367.605',
                'latitude' => -0.0238144,
                'longitude' => 110.3160694,
                'marker_type' => HguMarker::MARKER_TYPE_BETON,
                'condition' => HguMarker::CONDITION_RUSAK_BERAT,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(15)->toDateString(),
                'notes' => 'Contoh patok butuh tindak lanjut.',
            ],
            [
                'marker_number' => '305',
                'afdeling' => 8,
                'utm_coordinates' => '49M 425110 9995200',
                'latitude' => -0.0542000,
                'longitude' => 110.3278000,
                'marker_type' => HguMarker::MARKER_TYPE_PARALON,
                'condition' => HguMarker::CONDITION_BAIK,
                'is_moved' => false,
                'last_checked_at' => now()->subDays(5)->toDateString(),
                'notes' => 'Patok demo area Afdeling VIII.',
            ],
        ];
    }
}
