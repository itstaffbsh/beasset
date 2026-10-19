<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$dbName = config('database.connections.mysql.database');
$tables = DB::select("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = ?", [$dbName]);

foreach ($tables as $t) {
    $tableName = $t->TABLE_NAME;
    echo "=== Table: {$tableName} ===\n";
    $columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, EXTRA FROM information_schema.columns WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$dbName, $tableName]);
    foreach ($columns as $c) {
        if ($c->COLUMN_KEY === 'PRI' || str_ends_with($c->COLUMN_NAME, '_id') || $c->COLUMN_NAME === 'id') {
            echo "   * {$c->COLUMN_NAME} ({$c->COLUMN_TYPE}) key: {$c->COLUMN_KEY} extra: {$c->EXTRA}\n";
        }
    }
}
