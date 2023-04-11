<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Nestedset\QueryBuilder;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Ref;


/**
 * Custom query builder.
 */
final class Query
{
    /**
     * Custom query builder for contents to get content by page ID.
     *
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function contents( $rootValue, array $args ) : Builder
    {
        $ref = new Ref();
        $content = new Content();
        $limit = (int) ( $args['first'] ?? 15 );

        $builder = Content::select( $content->qualifyColumn( '*' ) )
            ->join( $ref->getTable(), $content->qualifyColumn( 'id' ), '=', $ref->qualifyColumn( 'content_id' ) )
            ->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        if( $pageid = $args['page_id'] ?? null )
        {
            $builder->where( $ref->qualifyColumn( 'page_id' ), $pageid )
                ->orderBy( $ref->qualifyColumn( 'position' ) );
        }
        else
        {
            $builder->orderBy( $content->qualifyColumn( 'id' ) );
        }

        return $builder;
}


    /**
     * Custom query builder for pages to get pages by parent ID.
     *
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function pages( $rootValue, array $args ) : QueryBuilder
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
