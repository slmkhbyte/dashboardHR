<?php

namespace Tests\Feature;

use App\Filament\Imports\HguMarkerImporter;
use App\Models\HguMarker;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HguMarkerImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_hgu_marker_import_accepts_sample_csv_headers_and_combines_utm_coordinates(): void
    {
        $import = $this->runImport([
            [
                'No Patok' => '1',
                'UTM 49 M sumbu X' => '420553',
                'UTM 49 M sumbu Y' => '9987669',
                'Koordinat Garis Bujur' => '110° 17\' 9,763" E',
                'Koordinat Garis Lintang' => '0° 6\' 41,589" S',
                'Jenis Patok' => 'Pt.Semen',
                'Keterangan' => 'Rusak',
                'Afdeling' => 'I',
            ],
            [
                'No Patok' => '2',
                'UTM 49 M sumbu X' => '420546',
                'UTM 49 M sumbu Y' => '9987918',
                'Koordinat Garis Bujur' => '110° 17\' 9,531" E',
                'Koordinat Garis Lintang' => '0° 6\' 33,476" S',
                'Jenis Patok' => 'Pt.Paralon',
                'Keterangan' => 'Baik',
                'Afdeling' => 'I',
            ],
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('hgu_markers', [
            'marker_number' => '1',
            'utm_coordinates' => '49M 420553 9987669',
            'marker_type' => 'beton',
            'condition' => 'rusak_ringan',
            'afdeling' => 1,
        ]);

        $marker = HguMarker::query()->where('marker_number', '1')->firstOrFail();

        $this->assertEqualsWithDelta(110.2860453, (float) $marker->longitude, 0.0000001);
        $this->assertEqualsWithDelta(-0.1115525, (float) $marker->latitude, 0.0000001);
    }

    public function test_no_patok_is_guessed_for_marker_number_mapping(): void
    {
        $markerNumberColumn = collect(HguMarkerImporter::getColumns())
            ->first(fn ($column): bool => $column->getName() === 'marker_number');

        $this->assertContains('no patok', $markerNumberColumn->getGuesses());
    }

    private function runImport(array $rows): Import
    {
        $user = User::factory()->create();

        $import = Import::query()->create([
            'file_name' => 'hgu-markers.csv',
            'file_path' => 'imports/hgu-markers.csv',
            'importer' => HguMarkerImporter::class,
            'processed_rows' => 0,
            'total_rows' => count($rows),
            'successful_rows' => 0,
            'user_id' => $user->id,
        ]);

        $job = new ImportCsv($import, $rows, [
            'marker_number' => 'No Patok',
            'utm_x' => 'UTM 49 M sumbu X',
            'utm_y' => 'UTM 49 M sumbu Y',
            'longitude' => 'Koordinat Garis Bujur',
            'latitude' => 'Koordinat Garis Lintang',
            'marker_type' => 'Jenis Patok',
            'condition' => 'Keterangan',
            'afdeling' => 'Afdeling',
        ]);

        $job->handle();

        return $import->refresh()->load('failedRows');
    }
}
