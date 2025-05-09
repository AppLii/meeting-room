<?php

namespace App\Database\Tables;

use App\Database\AbstractTable;
use App\Database\AbstractRecord;
use App\Database\Records\Room;
use Exception;

class RoomTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $NAME = 'name';
	private static string $SHORT_NAME = 'short_name';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('room');
	}

	/**
	 * 部屋を取得します。
	 *
	 * @param int $id 部屋ID
	 * @return Room 部屋オブジェクト
	 * @throws Exception レコードが見つからない場合、または取得に失敗した場合
	 */
	public function getRoom(int $id): Room
	{
		try {
			if ($id <= 0) {
				throw new Exception("Invalid room ID: must be a positive integer");
			}

			$rooms = $this->getRecordsByID($id);
			if (empty($rooms)) {
				throw new Exception("Room with ID {$id} not found");
			}
			return $rooms[0];
		} catch (Exception $e) {
			error_log(sprintf(
				"[RoomTable] Error retrieving room (ID: %d): %s",
				$id,
				$e->getMessage()
			));
			throw new Exception("Failed to retrieve room: " . $e->getMessage());
		}
	}

	/**
	 * すべての部屋を取得します。
	 *
	 * @return Room[] 部屋の配列
	 * @throws Exception 部屋の取得に失敗した場合
	 */
	public function getAllRooms(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * 部屋を作成します。
	 *
	 * @param Room $room 部屋データ
	 * @throws Exception
	 */
	public function addOrSetRoom(Room $room): void
	{
		$this->addOrSetRecord($room);
	}

	/**
	 * レコードを作成します。
	 *
	 * @param array $data レコードデータ
	 * @return AbstractRecord 作成されたレコード
	 * @throws Exception データが不足している場合、または型が誤っている場合
	 */
	protected function createRecord(array $data): AbstractRecord
	{
		try {
			if (!isset($data['name']) || !is_string($data['name']) || trim($data['name']) === '') {
				throw new Exception("Room name is required and must be a non-empty string");
			}

			if (!isset($data['short_name']) || !is_string($data['short_name']) || trim($data['short_name']) === '') {
				throw new Exception("Room short name is required and must be a non-empty string");
			}

			if (!isset($data['id']) || !is_int($data['id']) || $data['id'] <= 0) {
				throw new Exception("Room ID is required and must be a positive integer");
			}

			$room = new Room($data['name'], $data['short_name']);
			$room->id = $data['id'];
			return $room;
		} catch (Exception $e) {
			error_log(sprintf(
				"[RoomTable] Error creating room record: %s\nData: %s",
				$e->getMessage(),
				json_encode($data, JSON_UNESCAPED_UNICODE)
			));
			throw new Exception("Failed to create room record: " . $e->getMessage());
		}
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
