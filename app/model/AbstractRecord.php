<?php
require_once __DIR__ . '/_load.php';

/**
 * データベースレコードの基底抽象クラス
 * 
 * すべてのレコードクラスの親クラスとして機能し、
 * 基本的なデータアクセスとオブジェクト操作の共通機能を提供します。
 * このクラスを継承して具体的なテーブルのレコードクラスを実装します。
 * 
 * @package RSV\App
 * @author  RSV Development Team
 * @version 1.0.0
 * 
 */
abstract class AbstractRecord
{
	/**
	 * レコードの主キー
	 * 
	 * @var int|null レコードのID。新規作成時はnull
	 */
	protected ?int $id = null;

	/**
	 * レコードのIDを取得します
	 * 
	 * @return int レコードのID
	 */
	public function getId(): int
	{
		return $this->id;
	}


	/**
	 * レコードのid以外の値ついてバリデーションを行います。
	 * 
	 * @return bool バリデーションが成功した場合はtrue、失敗した場合はfalse
	 */
	abstract public function validate(): bool;


	/**
	 * レコードオブジェクトを配列に変換します。
	 *
	 * このメソッドはオブジェクトのすべてのプロパティを配列として返します。
	 * 子クラスで拡張して、特定のプロパティを除外したり、
	 * フォーマット変換を行ったりすることができます。
	 *
	 * @return array オブジェクトのプロパティを含む配列
	 * @throws \Exception 変換に失敗した場合
	 * 
	 * @note 継承先でのオーバーライド例:
	 * ```
	 * public function toArray(): array
	 * {
	 *     try {
	 *         return [
	 *             'id' => $this->id ?? null,
	 *             'name' => $this->name,
	 *             'email' => $this->email
	 *         ];
	 *     } catch (Throwable $e) {
	 *         error_log(sprintf(
	 *             "[User] Error converting user to array: %s",
	 *             $e->getMessage()
	 *         ));
	 *         throw new Exception("Failed to convert user data to array: " . $e->getMessage());
	 *     }
	 * }
	 * ```
	 */
	abstract public function toArray(): array;
	/**
	 * レコードがIDを持っているかチェックします。
	 *
	 * 新規レコードと既存レコードを区別するために使用されます。
	 * IDが設定されていれば既存レコード、設定されていなければ新規レコードと判断します。
	 *
	 * @return bool IDが設定されているかどうか
	 * 
	 * @note 使用例:
	 * ```
	 * if ($record->hasId()) {
	 *     // 既存レコードの更新処理
	 * } else {
	 *     // 新規レコードの挿入処理
	 * }
	 * ```
	 */
	public function hasId(): bool
	{
		return isset($this->id) && is_int($this->id) && $this->id > 0;
	}
}
