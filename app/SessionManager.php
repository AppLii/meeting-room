<?php


require_once __DIR__ . '/init.php';

/**
 * セッション管理クラス
 * 
 * ユーザーセッションの開始、検証、終了を管理し、
 * 認証状態の保持、CSRF対策、セッションセキュリティを担当します。
 * シングルトンパターンを採用しています。
 * 
 * @package RSV\App
 * @author  RSV Development Team
 * @version 1.0.0
 * 
 */
class SessionManager
{
	/**
	 * クラスのシングルトンインスタンス
	 * 
	 * @var self|null クラスのインスタンス、未初期化の場合はnull
	 */
	private static ?self $instance = null;

	/**
	 * プライベートコンストラクタ - シングルトンパターンの一部
	 *
	 * @throws Exception インスタンス化に失敗した場合
	 * 
	 * @note privateである理由:
	 *     - シングルトンパターンを実装するため
	 *     - 一貫したセッション管理を保証するため
	 */
	private function __construct()
	{
		// コンストラクタをプライベートにして外部からのインスタンス化を防ぐ
	}

	/**
	 * SessionManagerのシングルトンインスタンスを取得します。
	 *
	 * 未初期化の場合は新しいインスタンスを作成し、
	 * 既に初期化されている場合は既存のインスタンスを返します。
	 * これによりアプリケーション全体で同一のセッション管理が保証されます。
	 *
	 * @return self SessionManagerのインスタンス
	 * @throws Exception インスタンス取得に失敗した場合
	 * 
	 * @note staticである理由:
	 *     - グローバルなアクセスポイントを提供するため
	 *     - インスタンス化前にアクセス可能にするため
	 * 
	 * @example
	 * ```php
	 * $sessionManager = SessionManager::getInstance();
	 * $sessionManager->startSession();
	 * ```
	 */
	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 未完成
	 * ユーザーログイン処理を実行します。
	 * 
	 * 指定されたログインIDとパスワードを使用してユーザーの認証を行います。
	 * 認証成功時はセッションにユーザー情報を保存します。
	 *
	 * @param string $login_id ユーザーのログインID
	 * @param string $password ユーザーのパスワード
	 * @return bool 認証成功時はtrue、失敗時はfalseを返します。
	 * @throws Exception 認証失敗時またはセッションエラー発生時
	 */

	public function login(string $login_id, string $password): bool
	{
		try {
			// todo: ここで認証サーバとログイン処理を行う。


			assert($password === $_SERVER['REMOTE_USER']);


			if (session_status() === PHP_SESSION_ACTIVE) {
				return true; // セッションが既に開始している場合は何もしない
			}

			if (session_status() === PHP_SESSION_DISABLED) {
				throw new Exception("Sessions are disabled on this server");
			}

			if (!session_start()) {
				throw new Exception("Failed to start session");
			}

			// セッションIDの再生成（セッションハイジャック対策）
			if (!isset($_SESSION['initialized'])) {
				session_regenerate_id(true);
				$_SESSION['initialized'] = true;
			}

			// ログインIDとパスワードの検証
			$user = UserTable::getInstance()->getUserByEmail($login_id);
			// セッションにユーザー情報を保存
			$_SESSION['user_id'] = $user->getId();




			return true; //todo: 後でちゃんと実装する。
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] Login error: %s",
				$e->getMessage()
			));
			throw new Exception("Login failed: " . $e->getMessage());
		}
	}

	/**
	 * ユーザーログアウト処理を実行します。
	 * この関数を呼び出すと、login.phpにリダイレクトされ、後続のプログラムは実行されません。
	 * 内部的には、
	 * - セッションを破棄します。
	 * - セッションCookieを削除します。	
	 * - セッション変数をクリアします。
	 *
	 * @return void 返り値なし
	 * @throws Exception ログアウト処理中にエラーが発生した場合
	 */
	public function logout(): void
	{
		try {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				return; // セッションが開始していない場合は何もしない
			}

			// セッション変数をクリア
			$_SESSION = [];

			// セッションクッキーを削除
			if (isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time() - 3600, '/');
			}


			// セッションCookieを削除(より確実な方法らしい)
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(
					session_name(),
					'',
					time() - 42000,
					$params["path"],
					$params["domain"],
					$params["secure"],
					$params["httponly"]
				);
			}

			// セッションを破棄
			if (!session_destroy()) {
				throw new Exception("Failed to destroy session");
			}

			// ログインページにリダイレクト（キャッシュ制御ヘッダーを追加）
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("location: login.php");
			exit;

		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] Error ending session: %s",
				$e->getMessage()
			));
			throw new Exception("Session termination failed: " . $e->getMessage());
		}
	}


	/**
	 * セッションIDを再生成します。
	 * 
	 * セッションハイジャック対策として、現在のセッションデータを保持したまま
	 * 新しいセッションIDを生成します。ログイン後やセキュリティ上重要な
	 * 操作の前に呼び出すことが推奨されます。
	 *
	 * @return void 返り値なし
	 * @throws Exception セッションID再生成に失敗した場合
	 */
	public function regenarateSessionId(): void
	{
		try {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				throw new Exception("セッションが開始されていません");
			}
			$isSuccess = session_regenerate_id(true);
			if (!$isSuccess) {
				throw new Exception("セッションIDの再生成に失敗しました");
			}
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] セッションID再生成エラー: %s",
				$e->getMessage()
			));
			throw new Exception("セッションIDの再生成に失敗しました: " . $e->getMessage());
		}
	}

	/**
	 * セッションIDを取得します。
	 *
	 * 現在アクティブなセッションのIDを返します。
	 * セッションが開始されていない場合や無効な場合は例外がスローされます。
	 *
	 * @return string セッションID
	 * @throws Exception セッションが開始されていない場合、または無効な場合
	 * 
	 * @note セキュリティ上の注意:
	 *     - セッションIDは機密情報であり、公開するべきではありません
	 *     - デバッグ目的以外での使用は避けてください
	 */
	public function getSessionId(): string
	{
		try {
			if (!$this->hasSession()) {
				throw new Exception("Session has not been started or user is not authenticated");
			}

			// セッションIDの有効性確認
			if (session_status() !== PHP_SESSION_ACTIVE) {
				throw new Exception("Session is not active");
			}

			return session_id();
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] Error retrieving session ID: %s",
				$e->getMessage()
			));
			throw new Exception("Failed to retrieve session ID: " . $e->getMessage());
		}
	}

	/**
	 * CSRFトークンを生成して取得します
	 *
	 * @return string 生成されたCSRFトークン
	 * @throws Exception トークン生成に失敗した場合
	 */
	public function getCsrfToken(): string
	{
		try {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				throw new Exception("Session is not active");
			}

			if (!isset($_SESSION['csrf_token'])) {
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}

			return $_SESSION['csrf_token'];
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] CSRF token generation error: %s",
				$e->getMessage()
			));
			throw new Exception("CSRF token generation failed: " . $e->getMessage());
		}
	}

	/**
	 * CSRFトークンを検証します
	 *
	 * @param string $token 検証するトークン
	 * @return bool 検証結果
	 * @throws Exception トークン検証に失敗した場合
	 */
	public function checkCsrfToken(string $token): bool
	{
		try {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				throw new Exception("Session is not active");
			}

			if (!isset($_SESSION['csrf_token'])) {
				return false;
			}

			return hash_equals($_SESSION['csrf_token'], $token);
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] CSRF token verification error: %s",
				$e->getMessage()
			));
			throw new Exception("CSRF token verification failed: " . $e->getMessage());
		}
	}


	/**
	 * セッションが開始されているか、つまりログインされているかを確認します。
	 *
	 * @return bool セッションが開始されている場合はtrue、そうでない場合はfalseを返します。
	 * @throws Exception セッションの確認に失敗した場合
	 */
	public function hasSession(): bool
	{
		return isset($_SESSION['user_id']);
	}


	/**
	 * ユーザーを取得します。
	 *
	 * @return User ユーザーオブジェクトを返します。
	 * @throws Exception 認証されていない場合、またはユーザーが見つからない場合
	 */
	public function getSessionUser(): User
	{
		try {
			if (!isset($_SESSION['user_id'])) {
				throw new Exception("User is not authenticated");
			}

			$userId = $_SESSION['user_id'];

			try {
				return UserTable::getInstance()->getUser($userId);
			} catch (Exception $e) {
				throw new Exception("Could not retrieve user data: " . $e->getMessage());
			}
		} catch (Exception $e) {
			error_log(sprintf(
				"[SessionManager] Error retrieving session user: %s",
				$e->getMessage()
			));
			throw new Exception("Session user retrieval failed: " . $e->getMessage());
		}
	}
}
