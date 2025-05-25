<?php

class RsvTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $PJ_ID = 'pj_id';
	private static string $ROOM_ID = 'room_id';
	private static string $USER_ID = 'user_id';
	private static string $START_AT = 'start_at';
	private static string $FINISH_AT = 'finish_at';
	private static string $NOTE = 'note';
	private static string $RSVED_AT = 'rsved_at';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('rsv');
	}

	/**
	 * 予約を取得します。
	 *
	 * @param int $id 予約ID
	 * @return Rsv 予約オブジェクト
	 * @throws Exception
	 */
	public function getRsv(int $id): Rsv
	{
		$reservations = $this->getRecordsByID($id);
		if (empty($reservations)) {
			throw new Exception('Reservation not found.\n');
		}
		return $reservations[0];
	}

	/**
	 * すべての予約を取得します。
	 *
	 * @return Rsv[] 予約の配列
	 * @throws Exception 予約の取得に失敗した場合
	 */
	public function getAllRsvs(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * 予約を作成します。
	 *
	 * @param Rsv $rsv 予約データ
	 * @throws Exception
	 */
	public function addOrSetRsv(Rsv $rsv): void
	{
		$this->addOrSetRecord($rsv);
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
		$rsv = new Rsv(
			$data['pj_id'],
			$data['room_id'],
			$data['user_id'],
			new DateTime($data['start_at']),
			new DateTime($data['finish_at']),
			$data['note']
		);
		$rsv->id = $data['id'];
		return $rsv;
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
