# RSV Core モジュール

## 概要

RSV（Reservation System）コアモジュールは、予約システムの中核となる機能を提供するモジュール群です。データベース操作、セッション管理、ユーザー認証などの基本機能を抽象化し、堅牢で拡張性の高いシステム基盤を実現します。

このコアモジュールは以下の役割を担っています：
- データベースアクセス層の提供
- ユーザー認証・認可の管理
- データモデルの標準化
- 共通ユーティリティ機能の集約

## システム要件

?????????



## 主要コンポーネント

### データベース関連
- **Database.class.php**: データベース接続とクエリ実行を担当
- **AbstractRecord.class.php**: データベースレコードの基底クラス
- **AbstractTable.class.php**: データベーステーブル操作の基底クラス

### セッション管理
- **SessionManager.class.php**: ユーザーセッションと認証を管理

### ユーザー権限
- **UserRole.enum.php**: ユーザー権限の列挙型定義

### ユーティリティ
- **utilFunctions.php**: 共通ユーティリティ関数群

### レコードとテーブル
- **records/**: 各テーブルのレコードクラス（データモデル）
- **tables/**: 各テーブルの操作クラス

## ディレクトリ構造

```
core/
├── records/     # レコードクラス群
│   ├── Pj.class.php
│   ├── PjRoster.class.php
│   ├── Room.class.php
│   ├── RoomBlackout.class.php
│   ├── Rsv.class.php
│   ├── User.class.php
│   └── init.php
├── tables/      # テーブルクラス群
│   ├── PjTable.class.php
│   ├── PjRosterTable.class.php
│   ├── RoomTable.class.php
│   ├── RoomBlackoutsTable.class.php
│   ├── RsvTable.class.php
│   ├── UserTable.class.php
│   └── init.php
├── AbstractRecord.class.php
├── AbstractTable.class.php
├── Database.class.php
├── SessionManager.class.php
├── UserRole.enum.php
├── utilFunctions.php
└── init.php
```



## ライセンス

Copyright (c) 2023 RSV Development Team
All rights reserved.

本ソフトウェアの使用、複製、改変、配布については、開発チームの許可が必要です。
