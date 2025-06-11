<?php
require_once __DIR__ . '/../_load.php';
require_once __DIR__ . '/../app/util/utilFunctions.php';


$db = Database::getInstance();

$tables = $db->getAllTables();

foreach ($tables as $table) {
    print('<h2>' . $table->getTableName() . '</h2>');
    $assoc_array = [];
    $records = $table->getAllRecords();
    $assoc_array = array_map(fn($record) => $record->toArray(), $records);
    echoDataAsTable($assoc_array);
    // print_r($records);
}
