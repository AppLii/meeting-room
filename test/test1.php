<?php
use Database;

$db = Database::getInstance();

$tables = $db->getAllTables;

foreach ($tables as $table) {
	echoDataAsTable($table->getAllRecords);
}
