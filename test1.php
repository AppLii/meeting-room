<h1>All Tables</h1>

<?php

// メインの初期化ファイルのみインクルード
require_once __DIR__ . '/core/init.php';

try {
	$db = Database::getInstance();
	$db->init();

	// テーブルインスタンスを静的に取得
	$tables = $db->getAllTables();
	// 各テーブルに対して処理を実行
	foreach ($tables as $table) {
		$records = $table->getAllRecords();
		$records_array = [];
		foreach ($records as $record) {
			$records_array[] = $record->toArray();
		}
		echo "<h2>{$table->getTableName()}</h2>";
		echoDataAsTable($records_array);
	}
} catch (Exception $e) {
	echo $e->getMessage();
}
