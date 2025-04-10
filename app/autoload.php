<?php
/**
 * オートローディングスクリプト
 * 
 * composerがない環境でも動作するように、PSR-4準拠の簡易オートローダーを実装
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// エラーログの設定
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

// オートローダーの登録
spl_autoload_register(function ($class) {
    // デバッグ: 読み込もうとしているクラスを記録
    error_log("Attempting to load class: $class");
    
    // 名前空間のプレフィックスを定義
    $prefix = 'Core\\';
    
    // 比較用に小文字に変換
    $class_lower = strtolower($class);
    $prefix_lower = strtolower($prefix);

    // 名前空間のプレフィックスの長さを取得
    $prefix_len = strlen($prefix_lower);

    // もし要求されたクラス名がプレフィックスで始まらなければ処理しない
    if (strncmp($prefix_lower, $class_lower, $prefix_len) !== 0) {
        return;
    }

    // 名前空間のプレフィックスを取り除き、相対クラス名を取得
    $relative_class = substr($class, strlen($prefix));

    // 名前空間のセパレータを、ディレクトリセパレータに変換
    $file = __DIR__ . '/core/' . str_replace('\\', '/', strtolower($relative_class)) . '.php';
    
    // デバッグ: ファイルパスを記録
    error_log("Looking for file: $file");

    // もしファイルが存在すれば、それを読み込む
    if (file_exists($file)) {
        require $file;
        error_log("Successfully loaded: $file");
    } else {
        error_log("File not found: $file");
    }
});

try {
    // Databaseの初期化
    Core\Database\Database::getInstance()->init();
} catch (Exception $e) {
    error_log("Error initializing database: " . $e->getMessage());
    echo "Critical error: " . $e->getMessage();
} 