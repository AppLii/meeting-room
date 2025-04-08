<?php
/**
 * オートローディングスクリプト
 * 
 * composerがない環境でも動作するように、PSR-4準拠の簡易オートローダーを実装
 */

spl_autoload_register(function ($class) {
    // 名前空間のプレフィックスを定義
    $prefix = 'Core\\';

    // 名前空間のプレフィックスの長さを取得
    $prefix_len = strlen($prefix);

    // もし要求されたクラス名がプレフィックスで始まらなければ処理しない
    if (strncmp($prefix, $class, $prefix_len) !== 0) {
        return;
    }

    // 名前空間のプレフィックスを取り除き、相対クラス名を取得
    $relative_class = substr($class, $prefix_len);

    // 名前空間のセパレータを、ディレクトリセパレータに変換
    $file = __DIR__ . '/core/' . str_replace('\\', '/', $relative_class) . '.php';

    // もしファイルが存在すれば、それを読み込む
    if (file_exists($file)) {
        require $file;
    }
});

// Databaseの初期化
Core\Database\Database::getInstance()->init(); 