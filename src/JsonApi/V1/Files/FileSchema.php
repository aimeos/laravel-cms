<?php

namespace Aimeos\Cms\JsonApi\V1\Files;

use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Schema;
use Aimeos\Cms\Models\File;


class FileSchema extends Schema
{
    /**
     * Disables "self" links for element items.
     */
    protected bool $selfLink = false;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = File::class;


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
            Str::make( 'lang' )->readOnly(),
            Str::make( 'mime' )->readOnly(),
            Str::make( 'path' )->readOnly(),
            ArrayHash::make( 'previews' )->readOnly(),
            ArrayHash::make( 'description' )->readOnly(),
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
}
