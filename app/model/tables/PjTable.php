<?php

class PjTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $REP = 'rep';
	private static string $NAME = 'name';
	private static string $NICKNAME = 'nickname';
	private static string $MAX_RSV = 'max_rsv';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('pj');
	}

	/**
	 * プロジェクトを取得します。
	 *
	 * @param int $id プロジェクトID
	 * @return Pj プロジェクトオブジェクト
	 * @throws Exception
	 */
	public function getPj(int $id): Pj
	{
		$projects = $this->getRecordsByID($id);
		if (empty($projects)) {
			throw new Exception('Project not found.\n');
		}
		return $projects[0];
	}

	/**
	 * すべてのプロジェクトを取得します。
	 *
	 * @return Pj[] プロジェクトの配列
	 * @throws Exception プロジェクトの取得に失敗した場合
	 */
	public function getAllPjs(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * プロジェクトを作成します。
	 *
	 * @param Pj $pj プロジェクトデータ
	 * @throws Exception
	 */
	public function addOrSetPj(Pj $pj): void
	{
		$this->addOrSetRecord($pj);
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
		$pj = new Pj($data['name'], $data['nickname'], $data['max_rsv']);
		$pj->id = $data['id'];
		$pj->rep = $data['rep'];
		return $pj;
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
