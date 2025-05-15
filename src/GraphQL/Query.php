<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Element;
use Aimeos\Cms\Models\Page;


/**
 * Custom query builder.
 */
final class Query
{
    /**
     * Custom query builder for elements to search items by ID (optional).
     *
     * @param  null  $rootValue
     * @param  array  $args
     * @return \Kalnoy\Nestedset\QueryBuilder
     */
    public function elements( $rootValue, array $args ) : Builder
    {
        $limit = (int) ( $args['first'] ?? 100 );

        $builder = Element::withTrashed()
            ->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        if( !empty( $args['id'] ) ) {
            $builder->whereIn( 'id', $args['id'] );
        }

        return $builder;
    }


    /**
     * Custom query builder for pages to get pages by parent ID.
     *
     * @param  null  $rootValue
     * @param  array  $args
     * @return \Kalnoy\Nestedset\QueryBuilder
     */
    public function pages( $rootValue, array $args ) : \Kalnoy\Nestedset\QueryBuilder
    {
        $limit = (int) ( $args['first'] ?? 100 );

        $builder = Page::withTrashed()
            ->where( 'parent_id', $args['parent_id'] ?? null )
            ->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        if( isset( $args['lang'] ) ) {
            $builder->where( function( $query ) use ( $args ) {
                $query->select( 'lang' )
                    ->from( 'cms_versions' )
                    ->where( 'versionable_type', Page::class )
                    ->whereColumn( 'versionable_id', 'cms_pages.id' )
                    ->orderBy( 'id', 'desc' )
                    ->limit( 1 );
            }, (string) $args['lang'] );
        }

        return $builder;
    }


    /**
     * Custom query builder to search for pages.
     *
     * @param  null  $rootValue
     * @param  array  $args
     * @return \Kalnoy\Nestedset\QueryBuilder
     */
    public function searchPages( $rootValue, array $args ) : \Kalnoy\Nestedset\QueryBuilder
    {
        $value = $args['filter'] ?? '';
        $fields = ['config', 'domain', 'editor', 'meta', 'name', 'slug', 'tag', 'theme', 'title', 'to', 'type'];

        $limit = (int) ( $args['first'] ?? 100 );
        $builder = Page::withTrashed()
            ->where( function( $query ) use ( $fields, $value ) {
                $query->whereAny( $fields, 'like', '%' . $value . '%' )
                    ->orWhereHas('versions', function( Builder $query ) use ( $value ) {
                        $query->where( 'data', 'like', '%' . $value . '%' );
                    });
            } )
            ->skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        if( isset( $args['lang'] ) ) {
            $builder->where( function( $query ) use ( $args ) {
                $query->select( 'lang' )
                    ->from( 'cms_versions' )
                    ->where( 'versionable_type', Page::class )
                    ->whereColumn( 'versionable_id', 'cms_pages.id' )
                    ->orderBy( 'id', 'desc' )
                    ->limit( 1 );
            }, (string) $args['lang'] );
        }

        return $builder;
    }
}
