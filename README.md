## Composerのインストール手順

このプロジェクトでComposerを使うための手順をまとめます。

### 1. Composerのインストール

Linux環境の場合、以下のコマンドでComposerをインストールできます。

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

> ※ `composer.phar` がプロジェクト直下に生成されます。

### 2. オートロードファイルの生成

```
php composer.phar dump-autoload
```

これで `vendor/autoload.php` が生成されます。

### 3. PHPファイルでの利用方法

PHPファイルの先頭で以下を記述してください。

```
require_once __DIR__ . '/vendor/autoload.php';
```

### 4. 依存パッケージの追加

パッケージを追加したい場合は、以下のように実行します。

```
php composer.phar require <パッケージ名>
```

### 備考
- グローバルインストールしたい場合は、公式サイトの手順に従ってください。
- Windowsの場合は[公式サイト](https://getcomposer.org/)のインストーラーを利用してください。
