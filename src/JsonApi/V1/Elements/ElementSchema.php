<?php

namespace Aimeos\Cms\JsonApi\V1\Elements;

use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Schema;
use Aimeos\Cms\Models\Element;


class ElementSchema extends Schema
{
    /**
     * Default page value if no pagination was sent by the client.
     */
    protected ?array $defaultPagination = ['number' => 1];

    /**
     * Disables "self" links for element items.
     */
    protected bool $selfLink = false;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Element::class;


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
            Str::make( 'type' )->readOnly(),
            Str::make( 'lang' )->readOnly(),
            ArrayHash::make( 'data' )->readOnly()->extractUsing( function( $model, $column, $item ) {
                if( isset( $item->data->action ) ) {
                    $item->data->action = app()->call( $item->data->action, ['model' => $model, 'item' => $item] );
                }
                return $item;
            } ),
            HasMany::make( 'files' )->type( 'files' )->readOnly()->serializeUsing(
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
        return [];
    }


    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make()->withDefaultPerPage( 50 );
    }
}
