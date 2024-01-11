<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Vendor\Service;
use Illuminate\Support\Collection;

class MainSearchRepositoryRepository implements MainSearchRepositoryInterface
{
    public function find(
        string $locale,
        string $categoryId,
        float $latitude,
        float $longitude,
        int $tolerance,
        int $guestQty,
        string $dateFrom,
        string $dateTo,
        bool $all
    ): Collection {
        $result = Service::where([
            ['category_id', '=', $categoryId],
            ['max_guests_at_once', '>=', $guestQty],
            ['is_active', '=', true]
        ]);

        if ($all === false) {
            $result->whereRaw(
                'ST_DWithin(st_makepoint(latitude, longitude)::geography, st_makepoint(?, ?)::geography, ?)',
                [$latitude, $longitude, $tolerance]
            );
        }

        return $result
            ->with('reviews')
            ->get();
    }
}