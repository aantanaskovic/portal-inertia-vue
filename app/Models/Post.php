<?php

namespace App\Models;

use App\Filters\TitleOrContentFilter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Fillable(['title', 'content'])]
class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    public function scopeForIndex(Builder $query, ?Request $request = null): QueryBuilder
    {
        $req = $request ?? request();

        $searchScope = $req->input('filter.search_scope', 'both');

        $allowedRelations = ['user', 'userCount', 'userExists'];

        if ($req->has('include')) {
            $requestedIncludes = explode(',', $req->input('include'));

            $filteredIncludes = array_intersect($requestedIncludes, $allowedRelations);

            $req->merge([
                'include' => implode(',', $filteredIncludes)
            ]);
        } else {
            $query->with('user');
        }

        return QueryBuilder::for($query, $req)
            ->allowedFilters(
                AllowedFilter::custom('search', new TitleOrContentFilter($searchScope)),
                AllowedFilter::callback('search_scope', function (Builder $query, $value) {
                    // Empty but necessary, as this filter is needed to be passed through, 
                    // but not handled in any regular way, this is done in custom TitleOrContentFilter
                }),
                'title',
                'content'
            )
            ->allowedIncludes('user')
            ->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
