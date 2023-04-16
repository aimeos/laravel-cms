<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
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
        $tenant = \Aimeos\Cms\Tenancy::value();
        $limit = (int) ( $args['first'] ?? 15 );

        if( $pageid = $args['page_id'] ?? null )
        {
            $builder = Content::query()
                ->select( $content->qualifyColumn( '*' ) )
                ->from( 'cms_page_content' )
                ->join( $content->getTable(), $ref->qualifyColumn( 'content_id' ), '=', $content->qualifyColumn( 'id' ) )
                ->where( $ref->qualifyColumn( 'page_id' ), $pageid )
                ->where( $ref->qualifyColumn( 'tenant_id' ), $tenant )
                ->where( $content->qualifyColumn( 'tenant_id' ), $tenant )
                ->where( $ref->qualifyColumn( 'id' ), function( QueryBuilder $query ) use ( $ref, $pageid )  {
                    $query->select( DB::raw( 'MAX(id)' ) )
                        ->from( $ref->getTable() . ' AS maxlist' )
                        ->whereColumn( 'maxlist.page_id', $pageid )
                        ->whereColumn( 'maxlist.content_id', $ref->qualifyColumn( 'content_id' ) )
                        ->groupBy( 'maxlist.page_id', 'maxlist.content_id' );
                } )->orderBy( $ref->qualifyColumn( 'position' ) );
        }
        else
        {
            $builder = Content::orderBy( $content->qualifyColumn( 'id' ) );
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
