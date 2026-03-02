<?php

namespace App\Attributes;
use Attribute;
#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class IsGranted
{
    public string|array $permission;
    public array|string $params;

    public function __construct(string|array $permission, string|array $params = "")
    {
        $this->permission = $permission;
        $this->params = $params;
    }

    public function toMiddleware(): string
    {
        if (empty($this->params)) {
            return $this->permission;
        }

        $params = collect($this->params)
            ->map(fn ($v, $k) => "$k=$v")
            ->implode(',');
        return "{$this->permission}:{$params}";
    }
}
