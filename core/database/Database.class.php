<?php


require_once __DIR__ . '/init.php';

class Database
{
	private PDO $pdo;
	private static ?Database $instance = null;

	/**
	 * プライベートコンストラクタ - シングルトンパターンの一部です。
	 *
	 * 外部からのインスタンス化を防ぐため、コンストラクタをプライベートにしています。
	 * インスタンスの取得には getInstance() メソッドを使用してください。
	 */
	private function __construct() {}

	/**
	 * PDOExceptionをアプリケーション固有の例外に変換します
	 * 
	 * @param PDOException $e 元のPDO例外
	 * @param string $operation 実行しようとしていた操作の説明
	 * @return Exception 変換された例外
	 */
	private function handlePDOException(PDOException $e, string $operation): Exception
	{
		$errorInfo = $e->errorInfo ?? null;
		$errorCode = $errorInfo[1] ?? $e->getCode();

		$message = match ($errorCode) {
			1045 => 'Access denied to database (Authentication error)',
			1049 => 'Database does not exist',
			2002 => 'Could not connect to database server',
			2006 => 'Database connection was lost',
			'HY000' => 'Database access error occurred',
			default => 'Database error occurred'
		};

		error_log(sprintf(
			"[Database] PDO Exception: [%s] %s during operation '%s'\nError Info: %s\nStack trace:\n%s",
			$errorCode,
			$message,
			$operation,
			json_encode($errorInfo, JSON_UNESCAPED_UNICODE),
			$e->getTraceAsString()
		));

		return new Exception($message);
	}

	/**
	 * Databaseのシングルトンインスタンスを取得します。
	 *
	 * @return Database Databaseのインスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): Database
	{
		if (self::$instance === null) {
			self::$instance = new Database();
		}
		return self::$instance;
	}

	/**
	 * データベースに接続します。
	 *
	 * @throws Exception データベース接続に失敗した場合
	 */
	public function init(): void
	{
		try {
			$this->pdo = new PDO('sqlite:/var/db/meeting-room.sqlite');
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// SQLiteの外部キー制約を有効化
			$this->pdo->exec('PRAGMA foreign_keys = ON');
			// SQLiteのジャーナルモードをWALに設定（パフォーマンス向上）
			$this->pdo->exec('PRAGMA journal_mode = WAL');

			// テーブル存在チェックと初期化
			$this->ensureTablesExist();
		} catch (PDOException $e) {
			$this->handlePDOException($e, 'database connection');
		} catch (Exception $e) {
			error_log(sprintf(
				"[Database] Initialization error: [%s] %s\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				$e->getTraceAsString()
			));
			throw new Exception("Database initialization failed: " . $e->getMessage());
		}

		// 全テーブルを静的に初期化
		try {
			$this->getAllTables();
		} catch (Exception $e) {
			error_log(sprintf(
				"[Database] Error initializing tables: %s",
				$e->getMessage()
			));
			throw new Exception("Failed to initialize database tables: " . $e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	public function getAllTables(): array
	{
		try {
			return [
				UserTable::getInstance(),
				PjTable::getInstance(),
				PjRosterTable::getInstance(),
				RoomTable::getInstance(),
				RsvTable::getInstance(),
				BlackoutDefinitionTable::getInstance(),
				RoomBlackoutTable::getInstance(),
			];
		} catch (PDOException $e) {
			error_log(sprintf(
				"[Database] PDO Error while getting table list: [%s] %s",
				$e->getCode(),
				$e->getMessage()
			));
			throw $this->handlePDOException($e, 'getting table list');
		} catch (Exception $e) {
			error_log(sprintf(
				"[Database] Unexpected error while getting table list: [%s] %s\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				$e->getTraceAsString()
			));
			throw new Exception('Failed to get table list: ' . $e->getMessage() . "\n", (int)$e->getCode());
		}
	}

	/**
	 * PDOインスタンスを取得します。
	 *
	 * @return PDO PDOインスタンスを返します。
	 * @throws Exception データベース接続に失敗した場合
	 */
	public function getPDOInstance(): PDO
	{
		if (!isset($this->pdo)) {
			throw new Exception('Database connection is not initialized. Please call init() first.');
		}
		return $this->pdo;
	}

	/**
	 * テーブル名のリストを取得します。
	 *
	 * @return array テーブル名の配列を返します。
	 * @throws Exception データベース接続に失敗した場合
	 */
	public function getTableNames(): array
	{
		try {
			return $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			$this->handlePDOException($e, 'テーブル名一覧の取得');
			return []; // 例外が発生した場合は空配列を返す
		}
	}

	/**
	 * SQLクエリを準備し、ステートメントを返します
	 * 
	 * @param string $sql SQL文
	 * @return array{statement: PDOStatement, params: array<string, mixed>}
	 * @throws Exception
	 */
	public function prepare(string $sql): array
	{
		if (!isset($this->pdo)) {
			error_log("[Database] Attempted to prepare statement before initialization");
			throw new Exception('Database connection is not initialized. Please call init() first.');
		}

		try {
			$statement = $this->pdo->prepare($sql);
			if ($statement === false) {
				error_log(sprintf("[Database] Failed to prepare SQL statement: %s", $sql));
				throw new Exception("Failed to prepare query: $sql");
			}
			return ['statement' => $statement, 'params' => []];
		} catch (PDOException | TypeError | Throwable $e) {
			error_log(sprintf(
				"[Database] Error while preparing statement: [%s] %s\nSQL: %s\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				$sql,
				$e->getTraceAsString()
			));
			$message = match (true) {
				$e instanceof PDOException => $this->getErrorMessage($e),
				$e instanceof TypeError => "Invalid SQL statement provided",
				default => "Unexpected error occurred"
			};
			throw new Exception($message . ": " . $e->getMessage());
		}
	}

	/**
	 * パラメータをバインドします
	 * 
	 * @param array{statement: PDOStatement, params: array<string, mixed>} $prepared
	 * @param string $param パラメータ名
	 * @param mixed $value バインドする値
	 * @param int $type PDOパラメータ型
	 * @return array{statement: PDOStatement, params: array<string, mixed>}
	 * @throws Exception
	 */
	public function bind(array $prepared, string $param, mixed $value, int $type = PDO::PARAM_STR): array
	{
		try {
			if (!$prepared['statement']->bindValue($param, $value, $type)) {
				error_log(sprintf(
					"[Database] Failed to bind parameter: %s = %s (type: %d)",
					$param,
					var_export($value, true),
					$type
				));
				throw new Exception("Failed to bind parameter: $param");
			}
			$prepared['params'][$param] = $value;
			return $prepared;
		} catch (PDOException | TypeError | ValueError | Throwable $e) {
			error_log(sprintf(
				"[Database] Error while binding parameter: [%s] %s\nParameter: %s\nValue: %s\nType: %d\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				$param,
				var_export($value, true),
				$type,
				$e->getTraceAsString()
			));
			$message = match (true) {
				$e instanceof PDOException => $this->getErrorMessage($e),
				$e instanceof TypeError => "Invalid parameter type specified",
				$e instanceof ValueError => "Invalid parameter value specified",
				default => "Unexpected error occurred"
			};
			throw new Exception($message . ": " . $e->getMessage());
		}
	}

	/**
	 * PDOExceptionからエラーメッセージを取得します
	 * 
	 * @param PDOException $e PDO例外
	 * @return string エラーメッセージ
	 */
	private function getErrorMessage(PDOException $e): string
	{
		$errorInfo = $e->errorInfo ?? null;
		$errorCode = $errorInfo[1] ?? $e->getCode();

		error_log(sprintf(
			"[Database] SQL Error: [%s] %s\nError Info: %s",
			$errorCode,
			$e->getMessage(),
			json_encode($errorInfo, JSON_UNESCAPED_UNICODE)
		));

		return match ($errorCode) {
			'23000' => 'Unique constraint violation: Duplicate entry exists',
			'23502' => 'NOT NULL constraint violation: Required field is missing',
			'22001' => 'Data too long for column',
			'42S02' => "Table not found",
			'42S22' => 'Column not found',
			'23503' => 'Foreign key constraint violation: Referenced record exists',
			'HY000' => 'Error occurred during query execution',
			default => 'Database error occurred'
		};
	}

	/**
	 * クエリを実行し、結果を返します
	 * 
	 * @param array{statement: PDOStatement, params: array<string, mixed>} $prepared
	 * @return array<int, array<string, mixed>> 結果の配列
	 * @throws Exception
	 */
	public function execute(array $prepared): array
	{
		try {
			if (!$prepared['statement']->execute()) {
				error_log("[Database] Query execution failed");
				throw new Exception("Query execution failed");
			}
			return $prepared['statement']->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException | TypeError | ValueError | Throwable $e) {
			error_log(sprintf(
				"[Database] Error executing query: [%s] %s\nParameters: %s\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				json_encode($prepared['params'], JSON_UNESCAPED_UNICODE),
				$e->getTraceAsString()
			));
			$message = match (true) {
				$e instanceof PDOException => $this->getErrorMessage($e),
				$e instanceof TypeError => "Invalid data type",
				$e instanceof ValueError => "Invalid value specified",
				default => "Unexpected error occurred"
			};
			throw new Exception($message);
		}
	}

	/**
	 * 更新系クエリを実行します（INSERT/UPDATE/DELETE）
	 * 
	 * @param array{statement: PDOStatement, params: array<string, mixed>} $prepared
	 * @return int 影響を受けた行数
	 * @throws Exception
	 */
	public function executeUpdate(array $prepared): int
	{
		try {
			if (!$prepared['statement']->execute()) {
				error_log("[Database] Update query execution failed");
				throw new Exception("Query execution failed");
			}
			return $prepared['statement']->rowCount();
		} catch (PDOException | TypeError | ValueError | Throwable $e) {
			error_log(sprintf(
				"[Database] Error executing update: [%s] %s\nParameters: %s\nStack trace:\n%s",
				$e->getCode(),
				$e->getMessage(),
				json_encode($prepared['params'], JSON_UNESCAPED_UNICODE),
				$e->getTraceAsString()
			));
			$message = match (true) {
				$e instanceof PDOException => $this->getErrorMessage($e),
				$e instanceof TypeError => "Invalid data type",
				$e instanceof ValueError => "Invalid value specified",
				default => "Unexpected error occurred"
			};
			throw new Exception($message);
		}
	}

	/**
	 * テーブルが存在することを確認し、存在しない場合は作成します
	 * 
	 * アプリケーションで必要とされるすべてのテーブルが
	 * データベースに存在することを確認し、不足しているテーブルを
	 * 自動的に作成します。
	 * 
	 * @return void 返り値なし
	 * @throws Exception テーブル作成に失敗した場合
	 * 
	 * @note このメソッドは初期化時に自動的に呼び出されます。
	 *     - アプリケーション起動時に必要なテーブル構造を保証します。
	 *     - テーブル定義に変更がある場合は、このメソッドを更新する必要があります。
	 */
	private function ensureTablesExist(): void
	{
		try {
			$tables = $this->getTableNames();

			// 必要なテーブルのリスト
			$requiredTables = ['user', 'pj', 'pj_roster', 'room', 'rsv', 'blackout_definitions', 'room_blackouts'];

			foreach ($requiredTables as $table) {
				if (!in_array($table, $tables)) {
					// テーブルが存在しない場合は作成
					$this->createTable($table);
				}
			}
		} catch (PDOException $e) {
			$this->handlePDOException($e, 'checking/creating tables');
		}
	}

	/**
	 * 指定されたテーブルを作成します
	 * 
	 * テーブル名に応じた適切なスキーマでデータベーステーブルを
	 * 作成します。各テーブルの構造はプログラム内に定義されています。
	 * 
	 * @param string $tableName 作成するテーブル名
	 * @return void 返り値なし
	 * @throws Exception テーブル作成に失敗した場合
	 * 
	 * @note 新しいテーブルが必要な場合：
	 *     - このメソッド内のswitch文に新しいテーブル定義を追加してください
	 *     - 必要に応じてensureTablesExistのrequiredTablesリストも更新してください
	 */
	private function createTable(string $tableName): void
	{
		try {
			// テーブル作成のSQLを定義
			$sql = match ($tableName) {
				'user' => "CREATE TABLE user (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					name TEXT NOT NULL CHECK (length(name) < 128),
					email TEXT NOT NULL UNIQUE CHECK (length(email) < 256)
				) STRICT",
				'pj' => "CREATE TABLE pj (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					rep INTEGER NOT NULL,
					name TEXT NOT NULL CHECK (length(name) < 128),
					nickname TEXT NOT NULL CHECK (length(nickname) < 64),
					max_rsv INTEGER NOT NULL CHECK (max_rsv > 0),
					FOREIGN KEY (rep) REFERENCES user(id)
				) STRICT",
				'pj_roster' => "CREATE TABLE pj_roster (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					pj_id INTEGER NOT NULL,
					user_id INTEGER NOT NULL,
					FOREIGN KEY (pj_id) REFERENCES pj(id),
					FOREIGN KEY (user_id) REFERENCES user(id),
					UNIQUE(pj_id, user_id)
				) STRICT",
				'room' => "CREATE TABLE room (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					name TEXT NOT NULL CHECK (length(name) < 128),
					short_name TEXT NOT NULL CHECK (length(short_name) < 32)
				) STRICT",
				'rsv' => "CREATE TABLE rsv (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					pj_id INTEGER NOT NULL,
					room_id INTEGER NOT NULL,
					start_time TEXT NOT NULL,
					end_time TEXT NOT NULL,
					created_by INTEGER NOT NULL,
					CHECK (start_time < end_time),
					FOREIGN KEY (pj_id) REFERENCES pj(id),
					FOREIGN KEY (room_id) REFERENCES room(id),
					FOREIGN KEY (created_by) REFERENCES user(id)
				) STRICT",
				'blackout_definitions' => "CREATE TABLE blackout_definitions (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					name TEXT NOT NULL CHECK (length(name) < 128),
					description TEXT CHECK (description IS NULL OR length(description) < 512)
				) STRICT",
				'room_blackouts' => "CREATE TABLE room_blackouts (
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					room_id INTEGER NOT NULL,
					blackout_id INTEGER NOT NULL,
					start_time TEXT NOT NULL,
					end_time TEXT NOT NULL,
					CHECK (start_time < end_time),
					FOREIGN KEY (room_id) REFERENCES room(id),
					FOREIGN KEY (blackout_id) REFERENCES blackout_definitions(id)
				) STRICT",
				default => throw new Exception("Unknown table: $tableName")
			};

			$this->pdo->exec($sql);
			error_log("[Database] Created table: $tableName");
		} catch (PDOException $e) {
			$this->handlePDOException($e, "creating table $tableName");
		}
	}
}
