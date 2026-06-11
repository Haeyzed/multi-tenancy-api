<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait DeletesManyRecords
{
    /**
     * Delete multiple records by primary key inside a transaction.
     *
     * @param class-string<Model> $modelClass
     * @param list<int|string> $ids
     */
    protected function deleteManyByIds(string $modelClass, array $ids): int
    {
        return DB::transaction(function () use ($modelClass, $ids): int {
            $records = $modelClass::query()
                ->whereIn('id', $ids)
                ->get();

            $deleted = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $deleted++;
                }
            }

            return $deleted;
        });
    }
}
