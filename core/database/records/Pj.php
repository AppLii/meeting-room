<?php

namespace Core\Database\Records;

use Core\Database\AbstractRecord;
use Exception;
use Throwable;

class Pj extends AbstractRecord
{
    public int $id;
    public int $rep;
    private string $name;
    private string $nickname;
    private int $max_rsv;

    public function __construct(string $name, string $nickname, int $max_rsv)
    {
        if (empty($name)) {
            throw new Exception("Project name cannot be empty");
        }

        if (empty($nickname)) {
            throw new Exception("Project nickname cannot be empty");
        }

        if ($max_rsv <= 0) {
            throw new Exception("Maximum reservation count must be a positive number");
        }

        $this->name = $name;
        $this->nickname = $nickname;
        $this->max_rsv = $max_rsv;

        if (!$this->validate()) {
            throw new Exception("Invalid project data provided");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getMaxRsv(): int
    {
        return $this->max_rsv;
    }

    public function validate(): bool
    {
        if (empty($this->name) || empty($this->nickname)) {
            return false;
        }

        if ($this->max_rsv <= 0) {
            return false;
        }

        return true;
    }

    /**
     * プロジェクト情報を配列として返します。
     *
     * @return array プロジェクト情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'rep' => $this->rep ?? null,
                'name' => $this->name,
                'nickname' => $this->nickname,
                'max_rsv' => $this->max_rsv
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Pj] Error converting project to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert project data to array: " . $e->getMessage());
        }
    }
}
