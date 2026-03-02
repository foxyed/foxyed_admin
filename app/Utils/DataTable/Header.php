<?php

namespace App\Utils\DataTable;

class Header
{
    public function __construct(
        private readonly string $key,
        private readonly string $title,
        private readonly string $align,
        private readonly bool   $sortable = true,
    )
    {
    }


    public function toHeader(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'align' => $this->align,
            'sortable' => $this->sortable,
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public static function make($key, $title, $align, $sortable = true)
    {
        return new self($key, $title, $align, $sortable);
    }
}
