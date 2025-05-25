<?php

class PjRoster extends AbstractRecord
{
    public int $id;
    private int $user_id;
    private int $pj_id;

    public function __construct(int $user_id, int $pj_id)
    {
        $this->user_id = $user_id;
        $this->pj_id = $pj_id;

        if (!$this->validate()) {
            throw new Exception("Invalid project roster data provided");
        }
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getPjId(): int
    {
        return $this->pj_id;
    }

    public function validate(): bool
    {
        if ($this->user_id <= 0 || $this->pj_id <= 0) {
            return false;
        }

        return true;
    }

    /**
     * プロジェクトロースター情報を配列として返します。
     *
     * @return array プロジェクトロースター情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'user_id' => $this->user_id,
                'pj_id' => $this->pj_id
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[PjRoster] Error converting project roster to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert project roster data to array: " . $e->getMessage());
        }
    }
}
