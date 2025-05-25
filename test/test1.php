<?php
require_once __DIR__ . '/../_load.php';
require_once __DIR__ . '/../app/util/utilFunctions.php';


$db = Database::getInstance();

$tables = $db->getAllTables();

foreach ($tables as $table) {
	echoDataAsTable($table->getAllRecords());
}
