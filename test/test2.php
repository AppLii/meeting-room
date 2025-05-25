<?php
// コマンドライン実行のチェック
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    // コマンドライン以外で実行された場合は、プレーンテキスト形式で出力
    header('Content-Type: text/plain; charset=utf-8');
    // キャッシュを無効化
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

// プロジェクトのルートディレクトリを絶対パスとして設定
$rootDir = '/var/www/html/meeting-room';
$dataDir = $rootDir . '/app/model/sqlite';

/**
 * テキストに色を付ける（コマンドライン用）
 * 
 * @param string $text 色を付けるテキスト
 * @param string $color 色（red, green, yellow, blue, purple, cyan, white）
 * @return string 色付きテキスト
 */
function colorText($text, $color) {
    // コマンドライン以外では色付けしない
    if (php_sapi_name() !== 'cli') {
        return $text;
    }
    
    $colors = [
        'red'     => "\033[31m",
        'green'   => "\033[32m",
        'yellow'  => "\033[33m",
        'blue'    => "\033[34m",
        'purple'  => "\033[35m",
        'cyan'    => "\033[36m",
        'white'   => "\033[37m",
        'reset'   => "\033[0m",
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

/**
 * テーブルデータをテキスト形式で表示
 * 
 * @param array $columns カラム情報の配列
 * @param array $rows 行データの配列
 * @return string テキスト形式のテーブル
 */
function formatTextTable($columns, $rows) {
    if (empty($columns) || empty($rows)) {
        return "データがありません。\n";
    }
    
    // カラム名を取得
    $columnNames = array_map(function($col) {
        return $col['name'];
    }, $columns);
    
    // 各カラムの最大幅を計算
    $columnWidths = [];
    foreach ($columnNames as $i => $name) {
        $columnWidths[$name] = strlen($name);
    }
    
    // データの各セルの幅を考慮
    foreach ($rows as $row) {
        foreach ($columnNames as $name) {
            $value = $row[$name] ?? 'NULL';
            $valueWidth = strlen($value);
            if ($valueWidth > ($columnWidths[$name] ?? 0)) {
                $columnWidths[$name] = $valueWidth;
            }
        }
    }
    
    // テーブルのヘッダー行を構築
    $output = '';
    $separator = '+';
    $headerRow = '|';
    
    foreach ($columnNames as $name) {
        $width = $columnWidths[$name];
        $separator .= str_repeat('-', $width + 2) . '+';
        $headerRow .= ' ' . str_pad($name, $width, ' ') . ' |';
    }
    
    $output .= $separator . "\n";
    $output .= $headerRow . "\n";
    $output .= $separator . "\n";
    
    // テーブルの各行を構築
    foreach ($rows as $row) {
        $rowStr = '|';
        foreach ($columnNames as $name) {
            $value = $row[$name] ?? 'NULL';
            $width = $columnWidths[$name];
            $rowStr .= ' ' . str_pad($value, $width, ' ') . ' |';
        }
        $output .= $rowStr . "\n";
    }
    
    $output .= $separator . "\n";
    return $output;
}

// タイトル表示
echo colorText("=== データベース内容一覧 ===\n\n", 'cyan');

try {
    // SQLiteデータベースに直接接続（読み取り専用モード）
    try {
        $dbPath = $dataDir . '/meeting-room.sqlite';
        // データベースファイルが存在するか確認
        if (!file_exists($dbPath)) {
            echo colorText("データベースファイルが存在しません: {$dbPath}\n", 'red');
            exit(1);
        }
        
        // 接続
        $pdo = new \PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        echo colorText("データベース接続エラー: " . $e->getMessage() . "\n", 'red');
        exit(1);
    }
    
    // テーブル名の取得を直接SQLで行う
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tableNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tableNames)) {
        echo colorText("データベースにテーブルが存在しません。\n", 'yellow');
    } else {
        // 各テーブルの内容を表示
        foreach ($tableNames as $tableName) {
            // sqlite_sequenceなどのシステムテーブルはスキップ
            if (strpos($tableName, 'sqlite_') === 0) {
                continue;
            }
            
            echo colorText("\n【テーブル: {$tableName}】\n", 'green');
            
            // テーブルの構造を取得
            $stmt = $pdo->prepare("PRAGMA table_info({$tableName})");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo colorText("このテーブルにはカラムが存在しません。\n", 'yellow');
                continue;
            }
            
            // テーブルのデータを取得
            $stmt = $pdo->prepare("SELECT * FROM {$tableName}");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                echo colorText("このテーブルにはデータが存在しません。\n", 'yellow');
                continue;
            }
            
            // テーブル内容をテキスト形式で表示
            echo formatTextTable($columns, $rows);
        }
    }
} catch (Exception $e) {
    echo colorText("エラー: " . $e->getMessage() . "\n", 'red');
}

echo "\n" . colorText("=== 処理完了 ===\n", 'cyan');
