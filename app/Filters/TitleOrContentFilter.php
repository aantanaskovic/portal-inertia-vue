<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class TitleOrContentFilter implements Filter
{
    public function __construct(protected ?string $scope = null) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $scope = $this->scope ?? request()->input('filter.search_scope', 'both');

        $values = \is_array($value) ? $value : [$value];

        if (empty($values)) {
            return;
        }

        $query->where(function (Builder $subQuery) use ($values, $scope) {
            foreach ($values as $val) {
                if ($scope === 'title') {
                    $subQuery->where('title', 'LIKE', "%{$val}%");
                } elseif ($scope === 'content') {
                    $subQuery->where('content', 'LIKE', "%{$val}%");
                } else {
                    $subQuery->where(function (Builder $innerQuery) use ($val) {
                        $innerQuery->where('title', 'LIKE', "%{$val}%")
                            ->orWhere('content', 'LIKE', "%{$val}%");
                    });
                }
            }
        });
    }
}
