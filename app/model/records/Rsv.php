<?php


class Rsv extends AbstractRecord
{
    public int $id;
    private int $pj_id;
    private int $room_id;
    private int $user_id;
    private DateTime $start_at;
    private DateTime $finish_at;
    private ?string $note;

    public function __construct(int $pj_id, int $room_id, int $user_id, DateTime $start_at, DateTime $finish_at, ?string $note = null)
    {
        $this->pj_id = $pj_id;
        $this->room_id = $room_id;
        $this->user_id = $user_id;
        $this->start_at = $start_at;
        $this->finish_at = $finish_at;
        $this->note = $note;

        if (!$this->validate()) {
            throw new Exception("Invalid reservation data provided");
        }
    }

    public function getPjId(): int
    {
        return $this->pj_id;
    }

    public function getRoomId(): int
    {
        return $this->room_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getStartAt(): DateTime
    {
        return $this->start_at;
    }

    public function getFinishAt(): DateTime
    {
        return $this->finish_at;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * 予約情報を配列として返します。
     *
     * @return array 予約情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'pj_id' => $this->pj_id,
                'room_id' => $this->room_id,
                'user_id' => $this->user_id,
                'start_at' => $this->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $this->finish_at->format('Y-m-d H:i:s'),
                'note' => $this->note
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Rsv] Error converting reservation to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert reservation data to array: " . $e->getMessage());
        }
    }

    public function validate(): bool
    {
        if ($this->pj_id <= 0 || $this->room_id <= 0 || $this->user_id <= 0) {
            return false;
        }

        if ($this->start_at >= $this->finish_at) {
            return false;
        }

        return true;
    }
}
