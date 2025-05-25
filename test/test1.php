<?php
require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../app/util/utilFunctions.php';

$db = Database::getInstance();

$tables = $db->getAllTables();

foreach ($tables as $table) {
	echoDataAsTable($table->getAllRecords());
}
