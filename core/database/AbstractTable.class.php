<?php

include_once __DIR__ . '/init.php';

/**
 * データベーステーブル操作の基底抽象クラス
 * 
 * このクラスはデータベーステーブルへのアクセスと操作を抽象化し、
 * CRUD（作成、読取、更新、削除）操作の共通実装を提供します。
 * シングルトンパターンを採用し、テーブルごとに一意のインスタンスを保証します。
 *
 */
abstract class AbstractTable
{
	/**
	 * テーブル名
	 * 
	 * @var string データベース内のテーブル名
	 */
	private string $table_name;

	/**
	 * シングルトンインスタンスを保持する静的配列
	 * 
	 * @var array<string, AbstractTable> クラス名をキーとするインスタンスの配列
	 */
	private static array $instances = [];

	/**
	 * 【子クラス開発者向け】
	 * 
	 * このクラスを継承する際は、getInstance()メソッドを以下のように実装してください：
	 * 
	 * ```php
	 * public static function getInstance(): static
	 * {
	 *     return self::getInstanceInternal();
	 * }
	 * ```
	 * 
	 * これにより、子クラスはシングルトンパターンを簡単に実装できます。
	 * getInstanceInternal()メソッドは、クラスごとに一意のインスタンスを保証します。
	 */

	/**
	 * コンストラクタ
	 * 
	 * @param string $table_name データベーステーブル名
	 * 
	 * @note privateである理由:
	 *     - シングルトンパターンを強制するため
	 *     - インスタンス生成をgetInstance()メソッド経由に限定するため
	 */
	private function __construct(string $table_name)
	{
		$this->table_name = $table_name;
	}

	/**
	 * クローン禁止メソッド
	 * 
	 * シングルトンパターンを実装するため、クローンを禁止しています。
	 */
	private function __clone() {}

	/**
	 * INSERT文を実行するプライベートメソッド
	 * 
	 * @param array $values 挿入するカラムと値の連想配列
	 * @throws Exception データベースエラーが発生した場合
	 * 
	 * @note privateである理由:
	 *     - SQLインジェクション対策として直接呼び出しを制限
	 *     - addOrSetRecord()を通した安全な呼び出しを強制
	 */
	private function queryInsertSql(array $values): void
	{
		$db = Database::getInstance();
		$columns = array_keys($values);
		$placeholders = array_map(fn($col) => ":$col", $columns);
		$sql = "INSERT INTO $this->table_name (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ')';

		try {
			$prepared = $db->prepare($sql);
			foreach ($values as $key => $value) {
				$prepared = $db->bind($prepared, ":$key", $value);
			}
			$db->executeUpdate($prepared);
		} catch (PDOException $e) {
			$errorInfo = $e->errorInfo ?? null;
			$errorCode = $errorInfo[1] ?? $e->getCode();

			$message = match ($errorCode) {
				'23000' => 'Unique constraint violation: duplicate entry detected',
				'23502' => 'NOT NULL constraint violation: required field is missing',
				'22001' => 'Data is too long for the field',
				'42S02' => "Table '$this->table_name' does not exist",
				'42S22' => 'Specified column does not exist',
				default => 'Failed to insert data'
			};
			throw new Exception("{$message}: " . $e->getMessage());
		} catch (Exception $e) {
			throw new Exception("Failed to insert data: " . $e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	private function querySelectSql(array $where_conditions): array
	{
		$db = Database::getInstance();
		$where_clauses = array_map(fn($col) => "$col = :$col", array_keys($where_conditions));
		$sql = "SELECT * FROM $this->table_name";
		if (!empty($where_conditions)) {
			$sql .= " WHERE " . implode(' AND ', $where_clauses);
		}

		try {
			$prepared = $db->prepare($sql);
			foreach ($where_conditions as $key => $value) {
				// 値の型に基づいて適切なPDOパラメータ型を選択
				$type = match (true) {
					is_int($value) => PDO::PARAM_INT,
					is_bool($value) => PDO::PARAM_BOOL,
					is_null($value) => PDO::PARAM_NULL,
					default => PDO::PARAM_STR
				};
				$prepared = $db->bind($prepared, ":$key", $value, $type);
			}
			return $db->execute($prepared);
		} catch (PDOException $e) {
			$errorInfo = $e->errorInfo ?? null;
			$errorCode = $errorInfo[1] ?? $e->getCode();

			$message = match ($errorCode) {
				'42S02' => "Table '$this->table_name' does not exist",
				'42S22' => 'Specified column in query does not exist',
				'HY000' => 'Database query error occurred',
				default => 'Failed to retrieve data'
			};
			throw new Exception("{$message}: " . $e->getMessage());
		} catch (Exception $e) {
			throw new Exception("Failed to retrieve data: " . $e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	private function queryUpdateSql(array $values, array $where_conditions): void
	{
		$pdo = Database::getInstance()->getPDOInstance();
		try {
			$set_clauses = array_map(fn($col) => "$col = :set_$col", array_keys($values));
			$where_clauses = array_map(fn($col) => "$col = :where_$col", array_keys($where_conditions));
			$sql = "UPDATE $this->table_name SET " . implode(',', $set_clauses) . " WHERE " . implode(' AND ', $where_clauses);
			$stmt = $pdo->prepare($sql);

			// SETパラメータをバインド
			foreach ($values as $key => $value) {
				// 値の型に基づいて適切なPDOパラメータ型を選択
				$type = match (true) {
					is_int($value) => PDO::PARAM_INT,
					is_bool($value) => PDO::PARAM_BOOL,
					is_null($value) => PDO::PARAM_NULL,
					default => PDO::PARAM_STR
				};
				$stmt->bindValue(":set_$key", $value, $type);
			}

			// WHEREパラメータをバインド
			foreach ($where_conditions as $key => $value) {
				// 値の型に基づいて適切なPDOパラメータ型を選択
				$type = match (true) {
					is_int($value) => PDO::PARAM_INT,
					is_bool($value) => PDO::PARAM_BOOL,
					is_null($value) => PDO::PARAM_NULL,
					default => PDO::PARAM_STR
				};
				$stmt->bindValue(":where_$key", $value, $type);
			}

			$stmt->execute();
		} catch (PDOException $e) {
			$errorInfo = $e->errorInfo ?? null;
			$errorCode = $errorInfo[1] ?? $e->getCode();

			$message = match ($errorCode) {
				'23000' => 'Unique constraint violation: Duplicate entry exists',
				'23502' => 'NOT NULL constraint violation: Required field is missing',
				'22001' => 'Data is too long for the field',
				'42S02' => "Table '$this->table_name' does not exist",
				'42S22' => 'Specified column does not exist',
				default => 'Failed to update data'
			};
			throw new Exception("{$message}: " . $e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	private function queryDeleteSql(array $where_conditions): void
	{
		$pdo = Database::getInstance()->getPDOInstance();
		try {
			$where_clauses = array_map(fn($col) => "$col = :$col", array_keys($where_conditions));
			$sql = "DELETE FROM $this->table_name WHERE " . implode(' AND ', $where_clauses);
			$stmt = $pdo->prepare($sql);
			foreach ($where_conditions as $key => $value) {
				// 値の型に基づいて適切なPDOパラメータ型を選択
				$type = match (true) {
					is_int($value) => PDO::PARAM_INT,
					is_bool($value) => PDO::PARAM_BOOL,
					is_null($value) => PDO::PARAM_NULL,
					default => PDO::PARAM_STR
				};
				$stmt->bindValue(":$key", $value, $type);
			}
			$stmt->execute();
		} catch (PDOException $e) {
			$errorInfo = $e->errorInfo ?? null;
			$errorCode = $errorInfo[1] ?? $e->getCode();

			$message = match ($errorCode) {
				'23503' => 'Foreign key constraint violation: Related data exists',
				'42S02' => "Table '$this->table_name' does not exist",
				'42S22' => 'Specified column does not exist',
				default => 'Failed to delete data'
			};
			throw new Exception("{$message}: " . $e->getMessage());
		}
	}

	// プロテクテッドメソッド
	protected static function getInstanceInternal(string $table_name): static
	{
		$class = static::class;
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new static($table_name);
		}
		return self::$instances[$class];
	}

	/**
	 * 配列データからレコードオブジェクトを生成します。
	 * 
	 * データベースから取得した配列データを、適切なレコードオブジェクトに
	 * 変換するための抽象メソッドです。継承先で具体的な実装を行います。
	 *
	 * @param array $data レコードデータの連想配列
	 * @return AbstractRecord 生成されたレコードオブジェクト
	 * @throws Exception レコードの生成に失敗した場合
	 */
	abstract protected function createRecord(array $data): AbstractRecord;

	/**
	 * 単一の条件でレコードを取得します。
	 * 
	 * キーと値のペアで指定された条件に一致するレコードを
	 * データベースから取得します。
	 *
	 * @param string $key 条件となるカラム名
	 * @param mixed $value 検索する値
	 * @return array<AbstractRecord> 条件に一致するレコードオブジェクトの配列
	 * @throws Exception レコードの取得に失敗した場合
	 */
	protected function getRecordsByCondition(string $key, mixed $value): array
	{
		return $this->getRecordsByMultipleConditions([$key => $value]);
	}

	/**
	 * 単一カラムの複数値でレコードを取得します。
	 * 
	 * 指定されたカラムが複数の値のいずれかに一致するレコードを
	 * データベースから取得します。（INクエリ相当）
	 *
	 * @param string $key 条件となるカラム名
	 * @param array $values 検索する値の配列
	 * @return array<AbstractRecord> 条件に一致するレコードオブジェクトの配列
	 * @throws Exception レコードの取得に失敗した場合
	 */
	protected function getRecordsByConditionWithMultipleValues(string $key, array $values): array
	{
		$where_conditions = [];
		foreach ($values as $value) {
			$where_conditions[] = [$key => $value];
		}
		return $this->getRecordsByMultipleConditions($where_conditions);
	}

	/**
	 * 複数の条件でレコードを取得します。
	 * 
	 * 複数の条件に一致するレコードをデータベースから取得します。
	 * 条件が指定されない場合は、すべてのレコードを取得します。
	 *
	 * @param array $where_conditions カラム名と値のペアを含む連想配列
	 * @return array<AbstractRecord> 条件に一致するレコードオブジェクトの配列
	 * @throws Exception レコードの取得に失敗した場合
	 */
	protected function getRecordsByMultipleConditions(array $where_conditions = []): array
	{
		try {
			$instance = static::getInstance();
			$results = $instance->querySelectSql($where_conditions);
			$records = array_map(fn($data) => static::createRecord($data), $results);
			return array_filter($records, fn($record) => static::checkReadPermission($record));
		} catch (Exception $e) {
			error_log(sprintf(
				"[%s] Error retrieving entity: %s",
				static::class,
				$e->getMessage()
			));
			throw new Exception("Failed to retrieve entity: " . $e->getMessage());
		}
	}

	/**
	 * レコードを追加または更新します。
	 *
	 * @param AbstractRecord $record 追加または更新するレコード
	 * @return void 返り値なし
	 * @throws Exception 権限がない場合、または操作に失敗した場合
	 */
	protected function addOrSetRecord(AbstractRecord $record): void
	{
		if (!$this->checkWritePermission($record)) {
			throw new Exception('You do not have permission to update this record');
		}

		try {
			$data = $record->toArray();


			if ($record->hasId()) {
				// IDが存在するか確認

				// 非数値型や負の値のIDを防止
				if (isset($data['id']) && (!is_int($data['id']) || $data['id'] <= 0)) {
					throw new Exception('Invalid record ID: must be a positive integer');
				}

				if (!$this->has($data['id'])) {
					// IDが指定されているが存在しない場合はINSERT
					$this->queryInsertSql($data);
				} else {
					// IDが存在する場合はUPDATE
					$this->queryUpdateSql($data, ['id' => $data['id']]);
				}
			} else {
				// IDがない場合は新規追加
				if (isset($data['id'])) {
					// idキーは存在するが値が無効な場合は削除（auto-incrementに任せる）
					unset($data['id']);
				}
				$this->queryInsertSql($data);
			}
		} catch (Exception $e) {
			error_log(sprintf(
				"[%s] Error adding/updating record: %s",
				static::class,
				$e->getMessage()
			));
			throw new Exception('Failed to update record: ' . $e->getMessage());
		}
	}


	// パブリックメソッド
	public function __sleep(): array
	{
		throw new Exception('Singleton class cannot be serialized');
	}

	public function __wakeup(): void
	{
		throw new Exception('Singleton class cannot be unserialized');
	}

	public function getTableName(): string
	{
		return $this->table_name;
	}


	/**
	 * @throws Exception
	 */
	public function has(int $id): bool
	{
		$results = $this->querySelectSql(['id' => $id]);
		return !empty($results);
	}


	/**
	 * @throws Exception
	 */
	public function getAllRecords(): array
	{
		return $this->getRecordsByMultipleConditions();
	}
	public function getRecordsByID(int $id): array
	{
		return $this->getRecordsByCondition('id', $id);
	}

	public function getRecordsByMultipleIDs(array $ids): array
	{
		return $this->getRecordsByConditionWithMultipleValues('id', $ids);
	}

	/**
	 * 指定されたIDのレコードを削除します。
	 *
	 * @param int $id 削除するレコードのID
	 * @throws Exception 権限がない場合、レコードが存在しない場合、または削除に失敗した場合
	 */
	public function deleteRecordByID(int $id): void
	{
		try {
			if ($id <= 0) {
				throw new Exception("Invalid record ID: must be a positive integer");
			}

			$records = $this->getRecordsByID($id);
			if (empty($records)) {
				throw new Exception("Record with ID {$id} not found");
			}

			if (!$this->checkDeletePermission($records[0])) {
				throw new Exception("You do not have permission to delete this record");
			}

			$this->queryDeleteSql(['id' => $id]);
		} catch (Exception $e) {
			error_log(sprintf(
				"[%s] Error deleting record (ID: %d): %s",
				static::class,
				$id,
				$e->getMessage()
			));
			throw new Exception("Failed to delete record: " . $e->getMessage());
		}
	}



	abstract public static function getInstance(): static;

	abstract public function checkReadPermission(AbstractRecord $record): bool;

	abstract public function checkWritePermission(AbstractRecord $record): bool;

	abstract public function checkDeletePermission(AbstractRecord $record): bool;
}
