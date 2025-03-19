<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Stores;

use Hryha\RequestLogger\Data\FiltersData;
use Hryha\RequestLogger\Data\LogData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface Store
{
    public function list(FiltersData $filtersData): LengthAwarePaginator;

    public function one(int $id): mixed;

    public function create(LogData $logData): void;

    public function deleteOld(): void;

    public function deleteAll(): void;
}
