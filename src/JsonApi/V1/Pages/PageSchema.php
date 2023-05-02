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
use LaravelJsonApi\Eloquent\Fields\ArrayList;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Schema;
use Aimeos\Cms\Models\Page;


class PageSchema extends Schema
{
    /**
     * Default page value if no pagination was sent by the client.
     */
    protected ?array $defaultPagination = ['number' => 1];

    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 1;

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
     * Schema constructor.
     *
     * @param \LaravelJsonApi\Contracts\Server\Server $server
     */
    public function __construct( \LaravelJsonApi\Contracts\Server\Server $server )
    {
        parent::__construct( $server );
        $this->maxDepth = config( 'cms.jsonapi_maxdepth', 1 );
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
            Str::make( 'domain' )->readOnly(),
            Boolean::make( 'has' )->readOnly(),
            Number::make( 'cache' )->readOnly(),
            ArrayList::make( 'data' )->readOnly(),
            ArrayHash::make( 'meta' )->readOnly(),
            ArrayHash::make( 'config' )->readOnly(),
            DateTime::make( 'createdAt' )->readOnly(),
            DateTime::make( 'updatedAt' )->readOnly(),
            HasMany::make( 'contents' )->type( 'contents' )->readOnly()->serializeUsing(
                static fn($relation) => $relation->withoutLinks()
            ),
            HasOne::make( 'parent' )->type( 'pages' )->readOnly()->serializeUsing( function( $relation ) {
                if( $item = $relation->data() ) {
                    unset( $item->data, $item->meta );
                    $relation->withData( $item );
                }
                $relation->withoutLinks();
            }),
            HasMany::make( 'children' )->type( 'pages' )->readOnly()->serializeUsing( function( $relation ) {
                if( is_iterable( $data = $relation->data() ) ) {
                    foreach( $data as $item ) {
                        unset( $item->data, $item->meta );
                    }
                    $relation->withData( $data );
                }
                $relation->withoutLinks();
            }),
            HasMany::make( 'ancestors' )->type( 'pages' )->readOnly()->serializeUsing( function( $relation ) {
                if( is_iterable( $data = $relation->data() ) ) {
                    foreach( $data as $item ) {
                        unset( $item->data, $item->meta );
                    }
                    $relation->withData( $data );
                }
                $relation->withoutLinks();
            }),
            HasMany::make( 'subtree' )->type( 'pages' )->readOnly()->serializeUsing( function( $relation ) {
                if( is_iterable( $data = $relation->data() ) ) {
                    foreach( $data as $item ) {
                        unset( $item->data, $item->meta );
                    }
                    $relation->withData( $data );
                }
                $relation->withoutLinks();
            }),
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
            Where::make( 'domain' )->deserializeUsing(
                fn($value) => (string) $value
            ),
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
        if( $request && ( $filter = $request->get( 'filter' ) ) ) {
            return $query;
        }

        return $query->where( (new Page())->qualifyColumn( 'parent_id' ), null );
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
