<h1>All Tables</h1>

<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// メインの初期化ファイルのみインクルード
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/core/util/utilFunctions.php';

try {
	echo "<p>データベース接続を試みます...</p>";
	$db = Core\Database\Database::getInstance();
	echo "<p>データベースインスタンス取得成功</p>";
	$db->init();
	echo "<p>データベース初期化成功</p>";

	// テーブルインスタンスを静的に取得
	echo "<p>テーブル一覧取得を試みます...</p>";
	$tables = $db->getAllTables();
	echo "<p>テーブル一覧取得成功: " . count($tables) . "テーブル</p>";
	
	// 各テーブルに対して処理を実行
	foreach ($tables as $table) {
		echo "<p>テーブル処理: " . get_class($table) . "</p>";
		$records = $table->getAllRecords();
		echo "<p>レコード取得成功: " . count($records) . "件</p>";
		
		$records_array = [];
		foreach ($records as $record) {
			$records_array[] = $record->toArray();
		}
		echo "<h2>{$table->getTableName()}</h2>";
		echoDataAsTable($records_array);
	}
} catch (Exception $e) {
	echo "<h2>エラーが発生しました</h2>";
	echo "<p>メッセージ: " . $e->getMessage() . "</p>";
	echo "<p>コード: " . $e->getCode() . "</p>";
	echo "<p>ファイル: " . $e->getFile() . " (行: " . $e->getLine() . ")</p>";
	echo "<p>スタックトレース:</p>";
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
