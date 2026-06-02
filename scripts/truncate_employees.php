<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Bootstrap the application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $driver = DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    } elseif ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF');
    }

    DB::table('failed_import_rows')->truncate();
    DB::table('imports')->truncate();
    DB::table('employee_documents')->truncate();
    DB::table('employee_families')->truncate();
    DB::table('employee_histories')->truncate();
    DB::table('employees')->truncate();

    if ($driver === 'mysql') {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } elseif ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    echo "TRUNCATED\n";
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
