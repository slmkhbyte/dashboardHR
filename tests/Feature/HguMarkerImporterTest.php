<?php

namespace Tests\Feature;

use App\Filament\Imports\HguMarkerImporter;
use App\Models\HguMarker;
use App\Models\User;
use App\Observers\HguMarkerObserver;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
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

    public function test_hgu_marker_import_marks_duplicate_marker_numbers_in_same_file_as_failed_rows(): void
    {
        $import = $this->runImport([
            [
                'No Patok' => '218',
                'UTM 49 M sumbu X' => '428169.3784',
                'UTM 49 M sumbu Y' => '9997859.991',
                'Koordinat Garis Bujur' => '110° 21\' 16,164" E',
                'Koordinat Garis Lintang' => '0° 1\' 9,696" S',
                'Jenis Patok' => 'Pt.Semen',
                'Keterangan' => 'Baik',
                'Afdeling' => 'VI',
            ],
            [
                'No Patok' => '218',
                'UTM 49 M sumbu X' => '423893.8246',
                'UTM 49 M sumbu Y' => '9997367.605',
                'Koordinat Garis Bujur' => '110° 18\' 57,850" E',
                'Koordinat Garis Lintang' => '0° 1\' 25,732" S',
                'Jenis Patok' => 'Pt.Semen',
                'Keterangan' => 'Baik',
                'Afdeling' => 'VII',
            ],
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(1, $import->failedRows);
        $this->assertStringContainsString('duplicate marker number', strtolower($import->failedRows->first()->validation_error ?? ''));
        $this->assertDatabaseCount('hgu_markers', 1);
        $this->assertDatabaseHas('hgu_markers', [
            'marker_number' => '218',
            'afdeling' => 6,
        ]);
    }

    public function test_hgu_marker_import_preserves_unknown_marker_type_as_raw_value(): void
    {
        $import = $this->runImport([
            [
                'No Patok' => '132',
                'UTM 49 M sumbu X' => '426056',
                'UTM 49 M sumbu Y' => '9993887',
                'Koordinat Garis Bujur' => '110° 20\' 8,411" E',
                'Koordinat Garis Lintang' => '0° 3\' 59,982" S',
                'Jenis Patok' => 'Pt.Kayu',
                'Keterangan' => 'Hilang',
                'Afdeling' => 'III',
            ],
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);
        $this->assertDatabaseHas('hgu_markers', [
            'marker_number' => '132',
            'marker_type' => 'Pt.Kayu',
            'condition' => HguMarker::CONDITION_HILANG,
            'afdeling' => 3,
        ]);

        $this->assertSame('Pt.Kayu', HguMarker::getMarkerTypeOptions()['Pt.Kayu'] ?? null);
    }

    public function test_no_patok_is_guessed_for_marker_number_mapping(): void
    {
        $markerNumberColumn = collect(HguMarkerImporter::getColumns())
            ->first(fn ($column): bool => $column->getName() === 'marker_number');

        $this->assertContains('no patok', $markerNumberColumn->getGuesses());
    }

    public function test_hgu_marker_import_validates_out_of_bounds_coordinates(): void
    {
        $import = $this->runImport([
            [
                'No Patok' => '1',
                'UTM 49 M sumbu X' => '420553',
                'UTM 49 M sumbu Y' => '9987669',
                'Koordinat Garis Bujur' => '190.0', // Out of bounds longitude (> 180)
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
                'Koordinat Garis Lintang' => '-95.0', // Out of bounds latitude (< -90)
                'Jenis Patok' => 'Pt.Paralon',
                'Keterangan' => 'Baik',
                'Afdeling' => 'I',
            ],
        ]);

        $this->assertSame(0, $import->successful_rows);
        $this->assertCount(2, $import->failedRows);
    }

    public function test_hgu_marker_observer_logs_history_failures_without_interrupting_marker_persistence(): void
    {
        Log::spy();

        $marker = Mockery::mock(HguMarker::class)->makePartial();
        $marker->forceFill([
            'id' => 99,
            'marker_number' => '99',
            'marker_type' => HguMarker::MARKER_TYPE_BETON,
            'condition' => HguMarker::CONDITION_BAIK,
        ]);

        $relation = Mockery::mock();
        $relation->shouldReceive('create')->once()->andThrow(new \RuntimeException('history write failed'));

        $marker->shouldReceive('histories')->once()->andReturn($relation);

        (new HguMarkerObserver())->created($marker);

        Log::shouldHaveReceived('error')->once();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
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

    protected function tearDown(): void
    {
        Cache::flush();

        Mockery::close();

        parent::tearDown();
    }
}
