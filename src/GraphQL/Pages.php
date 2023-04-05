<?php

namespace Aimeos\Cms\GraphQL;

use Kalnoy\Nestedset\QueryBuilder;
use Aimeos\Cms\Models\Page;


/**
 * Custom query builder for pages to handle empty language values
 */
final class Pages
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function query( $rootValue, array $args ) : QueryBuilder
    {
        $limit = (int) ( $args['first'] ?? 15 );

        $builder = Page::withTrashed()
            ->where( 'parent_id', $args['parent_id'] ?? null )
            ->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        if( isset( $args['lang'] ) ) {
            $builder->where( 'lang', (string) $args['lang'] );
        }

        return $builder;
    }
}
