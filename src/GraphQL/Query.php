<?php

namespace Aimeos\Cms\GraphQL;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Element;
use Aimeos\Cms\Models\File;
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
        $filter = $args['filter'] ?? [];
        $limit = (int) ( $args['first'] ?? 100 );

        $builder = Element::skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        foreach( $args['sort'] ?? [] as $sort ) {
            $builder->orderBy( $sort['column'] ?? 'id', $sort['order'] ?? 'ASC' );
        }

        switch( $args['trashed'] ?? null ) {
            case 'without': $builder->withoutTrashed(); break;
            case 'with': $builder->withTrashed(); break;
            case 'only': $builder->onlyTrashed(); break;
        }

        if( !empty( $value = $filter['id'] ?? null ) ) {
            $builder->whereIn( 'id', $value );
        }

        if( !empty( $value = $filter['lang'] ?? null ) ) {
            $builder->where( 'lang', $value );
        }

        if( !empty( $value = $filter['editor'] ?? null ) ) {
            $builder->where( 'editor', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['type'] ?? null ) ) {
            $builder->where( 'type', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['name'] ?? null ) ) {
            $builder->where( 'name', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['data'] ?? null ) ) {
            $builder->where( 'data', 'like', '%' . $value . '%' );
        }

        if( !empty( $value = $filter['any'] ?? null ) ) {
            $builder->whereAny( ['name', 'data'], 'like', '%' . $value . '%' );
        }

        if( !empty( $filter ) )
        {
            $builder->orWhereHas('versions', function( $query ) use ( $filter ) {

                if( !empty( $value = $filter['id'] ?? null ) ) {
                    $query->whereIn( 'versionable_id', $value );
                }

                if( !empty( $value = $filter['lang'] ?? null ) ) {
                    $query->where( 'lang', $value );
                }

                if( !empty( $value = $filter['editor'] ?? null ) ) {
                    $query->where( 'editor', 'like', $value . '%' );
                }

                if( !empty( $value = $filter['type'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"type": "' . $value . '%' );
                }

                if( !empty( $value = $filter['name'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"name": "' . $value . '%' );
                }

                if( !empty( $value = $filter['data'] ?? null ) ) {
                    $query->where( 'data', 'like', '%' . $value . '%' );
                }

                if( !empty( $value = $filter['any'] ?? null ) ) {
                    $query->whereAny( ['name', 'data'], 'like', '%' . $value . '%' );
                }
            });
        }

        return $builder;
    }


    /**
     * Custom query builder for files to search for.
     *
     * @param  null  $rootValue
     * @param  array  $args
     * @return \Kalnoy\Nestedset\QueryBuilder
     */
    public function files( $rootValue, array $args ) : Builder
    {
        $filter = $args['filter'] ?? [];
        $limit = (int) ( $args['first'] ?? 100 );

        $builder = File::skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        foreach( $args['sort'] ?? [] as $sort ) {
            $builder->orderBy( $sort['column'] ?? 'id', $sort['order'] ?? 'ASC' );
        }

        switch( $args['trashed'] ?? null ) {
            case 'without': $builder->withoutTrashed(); break;
            case 'with': $builder->withTrashed(); break;
            case 'only': $builder->onlyTrashed(); break;
        }

        if( !empty( $value = $filter['id'] ?? null ) ) {
            $builder->whereIn( 'id', $value );
        }

        if( !empty( $value = $filter['mime'] ?? null ) ) {
            $builder->where( 'mime', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['tag'] ?? null ) ) {
            $builder->where( 'tag', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['name'] ?? null ) ) {
            $builder->where( 'name', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['editor'] ?? null ) ) {
            $builder->where( 'editor', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['any'] ?? null ) ) {
            $builder->whereAny( ['description', 'name', 'tag'], 'like', '%' . $value . '%' );
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
        $filter = $args['filter'] ?? [];
        $limit = (int) ( $args['first'] ?? 100 );
        $trashed = $args['trashed'] ?? null;

        $builder = Page::skip( max( ( $args['page'] ?? 1 ) - 1, 0 ) * $limit )
            ->take( min( max( $limit, 1 ), 100 ) );

        switch( $trashed ) {
            case 'without': $builder->withoutTrashed(); break;
            case 'with': $builder->withTrashed(); break;
            case 'only': $builder->onlyTrashed(); break;
        }

        if( $trashed !== 'only' ) {
            $builder->where( 'parent_id', $filter['parent_id'] ?? null );
        }

        if( !empty( $value = $filter['id'] ?? null ) ) {
            $builder->whereIn( 'id', $value );
        }

        if( !empty( $value = $filter['lang'] ?? null ) ) {
            $builder->where( 'lang', $value );
        }

        if( !empty( $value = $filter['to'] ?? null ) ) {
            $builder->where( 'to', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['slug'] ?? null ) ) {
            $builder->where( 'slug', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['domain'] ?? null ) ) {
            $builder->where( 'domain', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['name'] ?? null ) ) {
            $builder->where( 'name', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['title'] ?? null ) ) {
            $builder->where( 'title', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['theme'] ?? null ) ) {
            $builder->where( 'theme', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['tag'] ?? null ) ) {
            $builder->where( 'tag', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['editor'] ?? null ) ) {
            $builder->where( 'editor', 'like', $value . '%' );
        }

        if( !empty( $value = $filter['status'] ?? null ) ) {
            $builder->where( 'status', $value );
        }

        if( !empty( $value = $filter['cache'] ?? null ) ) {
            $builder->where( 'cache', $value );
        }

        if( !empty( $value = $filter['meta'] ?? null ) ) {
            $builder->where( 'meta', 'like', '%' . $value . '%' );
        }

        if( !empty( $value = $filter['config'] ?? null ) ) {
            $builder->where( 'config', 'like', '%' . $value . '%' );
        }

        if( !empty( $value = $filter['contents'] ?? null ) ) {
            $builder->where( 'contents', 'like', '%' . $value . '%' );
        }

        if( !empty( $value = $filter['any'] ?? null ) ) {
            $builder->whereAny( ['config', 'contents', 'meta', 'name', 'title'], 'like', '%' . $value . '%' );
        }

        if( !empty( $filter ) )
        {
            $builder->orWhereHas('versions', function( $query ) use ( $filter, $trashed ) {

                if( $trashed !== 'only' ) {
                    $query->where( 'cms_pages.parent_id', $filter['parent_id'] ?? null );
                }

                if( !empty( $value = $filter['id'] ?? null ) ) {
                    $query->whereIn( 'versionable_id', $value );
                }

                if( !empty( $value = $filter['lang'] ?? null ) ) {
                    $query->where( 'lang', $value );
                }

                if( !empty( $value = $filter['to'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"to": "' . $value . '%' );
                }

                if( !empty( $value = $filter['slug'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"slug": "' . $value . '%' );
                }

                if( !empty( $value = $filter['domain'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"domain": "' . $value . '%' );
                }

                if( !empty( $value = $filter['name'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"name": "' . $value . '%' );
                }

                if( !empty( $value = $filter['title'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"title": "' . $value . '%' );
                }

                if( !empty( $value = $filter['theme'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"theme": "' . $value . '%' );
                }

                if( !empty( $value = $filter['tag'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"tag": "' . $value . '%' );
                }

                if( !empty( $value = $filter['editor'] ?? null ) ) {
                    $query->where( 'data', 'like', $value . '%' );
                }

                if( !empty( $value = $filter['status'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"status": ' . $value );
                }

                if( !empty( $value = $filter['cache'] ?? null ) ) {
                    $query->where( 'data', 'like', '%"cache": ' . $value );
                }

                if( !empty( $value = $filter['meta'] ?? null ) ) {
                    $query->where( 'data', 'like', '%' . $value . '%' );
                }

                if( !empty( $value = $filter['config'] ?? null ) ) {
                    $query->where( 'data', 'like', '%' . $value . '%' );
                }

                if( !empty( $value = $filter['contents'] ?? null ) ) {
                    $query->where( 'contents', 'like', '%' . $value . '%' );
                }

                if( !empty( $value = $filter['any'] ?? null ) ) {
                    $query->whereAny( ['contents', 'data'], 'like', '%' . $value . '%' );
                }
            } );
        }

        return $builder;
    }
}
