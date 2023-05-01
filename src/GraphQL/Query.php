<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\Page;


/**
 * Custom query builder.
 */
final class Query
{
    /**
     * Custom query builder for pages to get pages by parent ID.
     *
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function pages( $rootValue, array $args ) : \Kalnoy\Nestedset\QueryBuilder
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
