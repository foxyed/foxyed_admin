<?php

namespace App\Utils\DataTable;

class Header
{
    public function __construct(
        private readonly string $key,
        private readonly string $title,
        private readonly string $align,
        private readonly bool   $sortable = true,
        private readonly bool   $searchable = true,
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
            'searchable' => $this->searchable,
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public static function make($key, $title, $align, $sortable = true, $searchable = true): self
    {
        return new self($key, $title, $align, $sortable, $searchable);
    }
}
