<?php

namespace Core\Database\Records;

use Core\Database\AbstractRecord;
use DateTime;
use Exception;
use Throwable;

class BlackoutDefinition extends AbstractRecord
{
    public int $id;
    private string $name;
    private DateTime $start_apply_at;
    private DateTime $finish_apply_at;

    public function __construct(string $name, DateTime $start_apply_at, DateTime $finish_apply_at)
    {
        $this->name = $name;
        $this->start_apply_at = $start_apply_at;
        $this->finish_apply_at = $finish_apply_at;

        if (!$this->validate()) {
            throw new Exception("Invalid blackout definition data provided");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartApplyAt(): DateTime
    {
        return $this->start_apply_at;
    }

    public function getFinishApplyAt(): DateTime
    {
        return $this->finish_apply_at;
    }

    public function validate(): bool
    {
        if (empty($this->name)) {
            return false;
        }

        if ($this->start_apply_at >= $this->finish_apply_at) {
            return false;
        }

        return true;
    }

    /**
     * 使用不可期間定義情報を配列として返します。
     *
     * @return array 使用不可期間定義情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'name' => $this->name,
                'start_apply_at' => $this->start_apply_at->format('Y-m-d H:i:s'),
                'finish_apply_at' => $this->finish_apply_at->format('Y-m-d H:i:s')
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[BlackoutDefinition] Error converting blackout definition to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert blackout definition data to array: " . $e->getMessage());
        }
    }
}
