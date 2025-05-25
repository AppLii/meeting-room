<?php

class Room extends AbstractRecord
{
    public int $id;
    private string $name;
    private string $short_name;

    public function __construct(string $name, string $short_name)
    {
        $this->name = $name;
        $this->short_name = $short_name;

        if (!$this->validate()) {
            throw new Exception("Invalid room data provided");
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortName(): string
    {
        return $this->short_name;
    }

    public function validate(): bool
    {
        if (empty($this->name) || empty($this->short_name)) {
            return false;
        }

        return true;
    }

    /**
     * 部屋情報を配列として返します。
     *
     * @return array 部屋情報の配列
     * @throws Exception 変換に失敗した場合
     */
    public function toArray(): array
    {
        try {
            return [
                'id' => $this->id ?? null,
                'name' => $this->name,
                'short_name' => $this->short_name
            ];
        } catch (Throwable $e) {
            error_log(sprintf(
                "[Room] Error converting room to array: %s",
                $e->getMessage()
            ));
            throw new Exception("Failed to convert room data to array: " . $e->getMessage());
        }
    }
}
