<?php


class RoomBlackoutTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $DEFINITION_ID = 'definition_id';
	private static string $ROOM_ID = 'room_id';
	private static string $START_AT = 'start_at';
	private static string $FINISH_AT = 'finish_at';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('room_blackout');
	}

	/**
	 * 部屋の停電を取得します。
	 *
	 * @param int $id 停電ID
	 * @return RoomBlackout 部屋の停電オブジェクト
	 * @throws Exception
	 */
	public function getRoomBlackout(int $id): RoomBlackout
	{
		$blackouts = $this->getRecordsByID($id);
		if (empty($blackouts)) {
			throw new Exception('Room blackout not found.\n');
		}
		return $blackouts[0];
	}

	/**
	 * すべての部屋の停電を取得します。
	 *
	 * @return RoomBlackout[] 部屋の停電の配列
	 * @throws Exception 部屋の停電の取得に失敗した場合
	 */
	public function getAllRoomBlackouts(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * 部屋の停電を作成します。
	 *
	 * @param RoomBlackout $blackout 部屋の停電データ
	 * @throws Exception
	 */
	public function addOrSetRoomBlackout(RoomBlackout $blackout): void
	{
		$this->addOrSetRecord($blackout);
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
		$blackout = new RoomBlackout(
			$data['definition_id'],
			$data['room_id'],
			new DateTime($data['start_at']),  // 文字列からDateTimeオブジェクトに変換
			new DateTime($data['finish_at'])  // 文字列からDateTimeオブジェクトに変換
		);
		$blackout->id = $data['id'];
		return $blackout;
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
