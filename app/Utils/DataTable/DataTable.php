<?php

namespace App\Utils\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class DataTable
{
    private array $headers = [];
    private Builder $builder;
    private int $page = 1;
    private int $perPage = 10;

    public static function of(Builder $builder): self
    {
        $dt = new self();
        $dt->builder = $builder;
        return $dt;
    }

    /** @param Header[] $headers */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function atPage(int $i = 1): self
    {
        $this->page = $i;
        return $this;
    }

    public function perPage(int $n = 10): self
    {
        $this->perPage = $n;
        return $this;
    }

    public function make(): JsonResponse
    {
        $query = clone $this->builder;

        // 🔎 Search globale
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                foreach ($this->headers as $header) {
                    $q->orWhere($header->getKey(), 'like', "%{$search}%");
                }
            });
        }

        // ↕ Sorting
        $sortBy = request('sortBy');
        $sortDesc = request('sortDesc') === 'true';

        $allowedSorts = array_map(fn($h) => $h->getKey(), $this->headers);

        if ($sortBy && in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        }

        // 📄 Pagination
        $page = request('page', $this->page);
        $perPage = request('itemsPerPage', $this->perPage);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'headers' => array_map(fn($h) => $h->toHeader(), $this->headers),
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
        ]);
    }
}
