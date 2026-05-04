<?php

namespace App\Core;

final class TabsDataList implements \JsonSerializable
{
    private array $list = [];
    private int|string|null $badge = 0;

    public function __construct(
        private string $key,
    ) {
    }

    public function addData(TabsData $data, bool $first = false)
    {
        if ($first) {
            array_unshift($this->list, $data);
        } else {
            $this->list[] = $data;
        }
        return $this;
    }

    public function setBadgeText(int|string|null $badge): TabsDataList
    {
        $this->badge = $badge;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'key' => $this->key,
            'badge' => $this->badge,
            'list' => $this->list,
        ];
    }
}
