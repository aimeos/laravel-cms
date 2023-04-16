<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
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

        $builder = Content::select( $content->qualifyColumn( '*' ) );

        if( $pageid = $args['page_id'] ?? null )
        {
            $lastref = DB::table( $ref->getTable() )
                ->select( DB::raw( 'MAX(id)' ), 'content_id', 'position' )
                ->where( 'page_id', $pageid )
                ->groupBy( 'page_id', 'content_id', 'position' );

            $builder->joinSub( $lastref, 'lastref', function ( JoinClause $join ) use ( $content, $ref ) {
                $join->on( $content->qualifyColumn( 'id' ), '=', 'lastref.content_id' );
            } )->orderBy( 'lastref.position' );
        }
        else
        {
            $builder->orderBy( $content->qualifyColumn( 'id' ) );
        }

        return $builder->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );
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
