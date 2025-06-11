<?php
require_once __DIR__ . '/_load.php';


class User extends AbstractRecord
{
    public int $id;
    private string $name;
    private string $email;

    /**
     * ユーザーを初期化します。
     *
     * @param string $name ユーザー名
     * @param string $email ユーザーのメールアドレス
     */
    function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;

        if (!$this->validate()) {
            throw new Exception("Invalid user data provided");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * ユーザー情報を配列として返します。
     *
     * @return array ユーザー情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'name' => $this->name,
                'email' => $this->email
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[User] Error converting user to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert user data to array: " . $e->getMessage());
        }
    }

    public function validate(): bool
    {
        if (empty($this->name)) {
            return false;
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }
}
