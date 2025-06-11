<?php
require_once __DIR__ . '/_load.php';


class RoomBlackout extends AbstractRecord
{
    public int $id;
    private int $definition_id;
    private int $room_id;
    private DateTime $start_at;
    private DateTime $finish_at;

    public function __construct(int $definition_id, int $room_id, DateTime $start_at, DateTime $finish_at)
    {
        $this->definition_id = $definition_id;
        $this->room_id = $room_id;
        $this->start_at = $start_at;
        $this->finish_at = $finish_at;

        if (!$this->validate()) {
            throw new Exception("Invalid room blackout data provided");
        }
    }

    public function getDefinitionId(): int
    {
        return $this->definition_id;
    }

    public function getRoomId(): int
    {
        return $this->room_id;
    }

    public function getStartAt(): DateTime
    {
        return $this->start_at;
    }

    public function getFinishAt(): DateTime
    {
        return $this->finish_at;
    }

    /**
     * 部屋使用不可期間情報を配列として返します。
     *
     * @return array 部屋使用不可期間情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'definition_id' => $this->definition_id,
                'room_id' => $this->room_id,
                'start_at' => $this->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $this->finish_at->format('Y-m-d H:i:s')
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[RoomBlackout] Error converting room blackout to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert room blackout data to array: " . $e->getMessage());
        }
    }

    public function validate(): bool
    {
        if ($this->definition_id <= 0 || $this->room_id <= 0) {
            return false;
        }

        if ($this->start_at >= $this->finish_at) {
            return false;
        }

        return true;
    }
}
