<?php

namespace Aimeos\Cms\JsonApi\V1\Resolvers;

use LaravelJsonApi\Core\Resolvers\FieldResolver;
use LaravelJsonApi\Contracts\Schema\Schema;


class CmsResolver extends FieldResolver
{
    public function resolveAttribute( Schema $schema, object $model, string $field )
    {
        if( $model instanceof \Aimeos\Cms\Models\Page && $field === 'contents' )
        {
            foreach( (array) $model->contents as $item )
            {
                if( isset( $item->data->action ) ) {
                    $item->data->action = app()->call( $item->data->action, ['page' => $model, 'item' => $item] );
                }
            }
        }

        return parent::resolveAttribute( $schema, $model, $field );
    }
}
