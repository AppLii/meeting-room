<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/util/utilFunctions.php';

use App\Database\Database;

$db = Database::getInstance();

$tables = $db->getAllTables();

foreach ($tables as $table) {
	echoDataAsTable($table->getAllRecords());
}
