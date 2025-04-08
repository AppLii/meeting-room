<?php

namespace Core\Database\Tables;

use Core\Database\AbstractTable;
use Core\Database\AbstractRecord;
use Core\Database\Records\BlackoutDefinition;
use DateTime;
use Exception;

class BlackoutDefinitionTable extends AbstractTable
{
	private static string $ID = 'id';
	private static string $NAME = 'name';
	private static string $START_APPLY_AT = 'start_apply_at';
	private static string $FINISH_APPLY_AT = 'finish_apply_at';

	/**
	 * インスタンスを取得します。
	 *
	 * @return static インスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 */
	public static function getInstance(): static
	{
		return self::getInstanceInternal('blackout_definition');
	}

	/**
	 * 停電定義を取得します。
	 *
	 * @param int $id 定義ID
	 * @return BlackoutDefinition 停電定義オブジェクト
	 * @throws Exception
	 */
	public function getBlackoutDefinition(int $id): BlackoutDefinition
	{
		$definitions = $this->getRecordsByID($id);
		if (empty($definitions)) {
			throw new Exception('Blackout definition not found.\n');
		}
		return $definitions[0];
	}

	/**
	 * すべての停電定義を取得します。
	 *
	 * @return BlackoutDefinition[] 停電定義の配列
	 * @throws Exception 停電定義の取得に失敗した場合
	 */
	public function getAllBlackoutDefinitions(): array
	{
		return $this->getAllRecords();
	}

	/**
	 * 停電定義を作成します。
	 *
	 * @param BlackoutDefinition $definition 停電定義データ
	 * @throws Exception
	 */
	public function addOrSetBlackoutDefinition(BlackoutDefinition $definition): void
	{
		$this->addOrSetRecord($definition);
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
		$definition = new BlackoutDefinition(
			$data['name'],
			new DateTime($data['start_apply_at']),  // 文字列からDateTimeオブジェクトに変換
			new DateTime($data['finish_apply_at'])  // 文字列からDateTimeオブジェクトに変換
		);
		$definition->id = $data['id'];
		return $definition;
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
