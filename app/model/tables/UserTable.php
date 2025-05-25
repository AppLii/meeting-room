<?php
require_once __DIR__ . '/_load.php';

class UserTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $EMAIL = 'email';
	private static string $NAME = 'name';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('user');
	}


	/**
	 * ユーザーを取得します。
	 *
	 * @param int $id ユーザーID
	 * @return User ユーザーオブジェクト
	 * @throws Exception
	 */
	public function getUser(int $id): User
	{
		try {
			$users = $this->getRecordsByID($id);
			if (empty($users)) {
				throw new Exception("User with ID {$id} not found");
			}
			return $users[0];
		} catch (Exception $e) {
			error_log(sprintf("[UserTable] Error retrieving user (ID: %d): %s", $id, $e->getMessage()));
			throw new Exception("Failed to retrieve user: " . $e->getMessage());
		}
	}

	public function getUserByEmail(string $email): User
	{
		$users = $this->getRecordsByCondition(self::$EMAIL, $email);
		if (empty($users)) {
			throw new Exception("User with email {$email} not found");
		}
		return $users[0];
	}

	/**
	 * すべてのユーザーを取得します。
	 *
	 * @return User[] ユーザーの配列
	 * @throws Exception ユーザーの取得に失敗した場合
	 */
	public function getAllUsers(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * ユーザーを作成します。
	 *
	 * @param User $user ユーザーデータ
	 * @throws Exception
	 */
	public function addOrSetUser(User $user): void
	{
		$this->addOrSetRecord($user);
	}


	/**
	 * レコードを作成します。
	 *
	 * @param array $data レコードデータ
	 * @return AbstractRecord 作成されたレコード
	 * @throws Exception
	 */
	protected function createRecord(array $data): AbstractRecord
	{
		$user = new User($data['name'], $data['email']);
		$user->id = $data['id'];
		return $user;
	}


	/**
	 * 読み取り権限を確認します。
	 *
	 * @param AbstractRecord $record 確認するレコード
	 * @return bool 読み取り可能かどうか
	 */
	public function checkReadPermission(AbstractRecord $record): bool
	{
		// 読み取り権限のロジックをここに実装
		return true;
	}

	/**
	 * 書き込み権限を確認します。
	 *
	 * @param AbstractRecord $record 確認するレコード
	 * @return bool 書き込み可能かどうか
	 */
	public function checkWritePermission(AbstractRecord $record): bool
	{
		// 書き込み権限のロジックをここに実装
		return true;
	}

	/**
	 * 削除権限を確認します。
	 *
	 * @param AbstractRecord $record 確認するレコード
	 * @return bool 削除可能かどうか
	 */
	public function checkDeletePermission(AbstractRecord $record): bool
	{
		// 削除権限のロジックをここに実装
		return true;
	}
}
