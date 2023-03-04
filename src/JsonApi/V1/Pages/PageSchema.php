<?php

namespace Aimeos\Cms\JsonApi\V1\Pages;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Schema;
use Aimeos\Cms\Models\Page;


class PageSchema extends Schema
{
    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 2;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Page::class;


    /**
     * Determine if the resource is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }


    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make( 'parent_id' )->readOnly(),
            Str::make( 'lang' )->readOnly(),
            Str::make( 'slug' )->readOnly(),
            Str::make( 'name' )->readOnly(),
            Str::make( 'title' )->readOnly(),
            Str::make( 'tag' )->readOnly(),
            Str::make( 'to' )->readOnly(),
            Number::make( 'cache' )->readOnly(),
            ArrayHash::make( 'data' )->readOnly(),
            DateTime::make( 'createdAt' )->readOnly(),
            DateTime::make( 'updatedAt' )->readOnly(),
            HasOne::make( 'parent' )->type( 'pages' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
            HasMany::make( 'children' )->type( 'pages' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
            HasMany::make( 'ancestors' )->type( 'pages' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
            HasMany::make( 'descendants' )->type( 'pages' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
            BelongsToMany::make( 'content' )->type( 'contents' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
        ];
    }


    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            Where::make( 'tag' )->deserializeUsing(
                fn($value) => (string) $value
            ),
            Where::make( 'lang' )->deserializeUsing(
                fn($value) => (string) $value
            ),
            WhereIdIn::make( $this ),
        ];
    }


    /**
     * Build an index query for this resource.
     *
     * @param Request|null $request
     * @param Builder $query
     * @return Builder
     */
    public function indexQuery( ?Request $request, Builder $query ): Builder
    {
        $query = $query->withDepth();

        if( $request && ( $filter = $request->get( 'filter' ) ) ) {
            return $query;
        }

        return $query->where( 'cms_pages.parent_id', null );
    }


    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
