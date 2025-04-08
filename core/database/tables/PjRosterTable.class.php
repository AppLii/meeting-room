<?php

namespace Core\Database\Tables;

use Core\Database\AbstractTable;
use Core\Database\AbstractRecord;
use Core\Database\Records\PjRoster;
use Exception;

class PjRosterTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $USER_ID = 'user_id';
	private static string $PJ_ID = 'pj_id';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('pj_roster');
	}

	/**
	 * プロジェクトロスターを取得します。
	 *
	 * @param int $id ロスターID
	 * @return PjRoster プロジェクトロスターオブジェクト
	 * @throws Exception
	 */
	public function getPjRoster(int $id): PjRoster
	{
		$rosters = $this->getRecordsByID($id);
		if (empty($rosters)) {
			throw new Exception('Project roster not found.\n');
		}
		return $rosters[0];
	}

	/**
	 * すべてのプロジェクトロスターを取得します。
	 *
	 * @return PjRoster[] プロジェクトロスターの配列
	 * @throws Exception プロジェクトロスターの取得に失敗した場合
	 */
	public function getAllPjRosters(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * プロジェクトロスターを作成します。
	 *
	 * @param PjRoster $pjRoster プロジェクトロスターデータ
	 * @throws Exception
	 */
	public function addOrSetPjRoster(PjRoster $pjRoster): void
	{
		$this->addOrSetRecord($pjRoster);
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
		$pjRoster = new PjRoster($data['user_id'], $data['pj_id']);
		$pjRoster->id = $data['id'];
		return $pjRoster;
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
