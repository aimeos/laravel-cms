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

        switch( $args['publish'] ?? null )
        {
            case 'PUBLISHED':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_elements.id' )
                        ->where( 'cms_versions.versionable_type', Element::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->havingRaw('published = 1 OR published IS NULL');
                break;
            case 'DRAFT':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_elements.id' )
                        ->where( 'cms_versions.versionable_type', Element::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->having( 'published', false );
                break;
            case 'SCHEDULED':
                $builder->whereHas('versions', function( $query ) {
                    $query->where( 'versionable_type', Element::class )
                        ->where( 'publish_at', '!=', null )
                        ->where( 'published', false );
                });
                break;
        }

        if( !empty( $filter ) )
        {
            $builder->where( function( $builder ) use ( $filter ) {

                if( array_key_exists( 'id', $filter ) ) {
                    $builder->whereIn( 'id', $filter['id'] );
                }

                if( array_key_exists( 'lang', $filter ) ) {
                    $builder->where( 'lang', $filter['lang'] );
                }

                if( array_key_exists( 'type', $filter ) ) {
                    $builder->where( 'type', (string) $filter['type'] );
                }

                if( array_key_exists( 'name', $filter ) ) {
                    $builder->where( 'name', 'like', $filter['name'] . '%' );
                }

                if( array_key_exists( 'editor', $filter ) ) {
                    $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                }

                if( array_key_exists( 'data', $filter ) ) {
                    $builder->where( 'data', 'like', '%' . $filter['data'] . '%' );
                }

                if( array_key_exists( 'any', $filter ) ) {
                    $builder->whereAny( ['name', 'data'], 'like', '%' . $filter['any'] . '%' );
                }

                $builder->orWhereHas('versions', function( $builder ) use ( $filter ) {

                    if( array_key_exists( 'id', $filter ) ) {
                        $builder->whereIn( 'versionable_id', $filter['id'] );
                    }

                    if( array_key_exists( 'lang', $filter ) ) {
                        $builder->where( 'lang', $filter['lang'] );
                    }

                    if( array_key_exists( 'editor', $filter ) ) {
                        $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                    }

                    if( array_key_exists( 'type', $filter ) ) {
                        $builder->where( 'data->type', (string) $filter['type'] );
                    }

                    if( array_key_exists( 'name', $filter ) ) {
                        $builder->where( 'data->name', 'like', $filter['name'] . '%' );
                    }

                    if( array_key_exists( 'data', $filter ) ) {
                        $builder->where( 'data', 'like', '%' . $filter['data'] . '%' );
                    }

                    if( array_key_exists( 'any', $filter ) ) {
                        $builder->whereAny( ['name', 'data'], 'like', '%' . $filter['any'] . '%' );
                    }
                });
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

        switch( $args['publish'] ?? null )
        {
            case 'PUBLISHED':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_files.id' )
                        ->where( 'cms_versions.versionable_type', File::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->havingRaw('published = 1 OR published IS NULL');
                break;
            case 'DRAFT':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_files.id' )
                        ->where( 'cms_versions.versionable_type', File::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->having( 'published', false );
                break;
            case 'SCHEDULED':
                $builder->whereHas('versions', function( $query ) {
                    $query->where( 'versionable_type', File::class )
                        ->where( 'publish_at', '!=', null )
                        ->where( 'published', false );
                });
                break;
        }

        if( !empty( $filter ) )
        {
            $builder->where( function( $builder ) use ( $filter ) {

                if( array_key_exists( 'id', $filter ) ) {
                    $builder->whereIn( 'id', $filter['id'] );
                }

                if( array_key_exists( 'lang', $filter ) ) {
                    $builder->where( 'lang', $filter['lang'] );
                }

                if( array_key_exists( 'mime', $filter ) ) {
                    $builder->where( 'mime', 'like', $filter['mime'] . '%' );
                }

                if( array_key_exists( 'name', $filter ) ) {
                    $builder->where( 'name', 'like', $filter['name'] . '%' );
                }

                if( array_key_exists( 'editor', $filter ) ) {
                    $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                }

                if( array_key_exists( 'any', $filter ) ) {
                    $builder->whereAny( ['description', 'name'], 'like', '%' . $filter['any'] . '%' );
                }

                $builder->orWhereHas('versions', function( $builder ) use ( $filter ) {

                    if( array_key_exists( 'id', $filter ) ) {
                        $builder->whereIn( 'versionable_id', $filter['id'] );
                    }

                    if( array_key_exists( 'lang', $filter ) ) {
                        $builder->where( 'lang', $filter['lang'] );
                    }

                    if( array_key_exists( 'editor', $filter ) ) {
                        $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                    }

                    if( array_key_exists( 'mime', $filter ) ) {
                        $builder->where( 'data->mime', 'like', $filter['mime'] . '%' );
                    }

                    if( array_key_exists( 'name', $filter ) ) {
                        $builder->where( 'data->name', 'like', $filter['name'] . '%' );
                    }

                    if( array_key_exists( 'description', $filter ) ) {
                        $builder->where( 'data->description', 'like', '%' . $filter['description'] . '%' );
                    }

                    if( array_key_exists( 'any', $filter ) ) {
                        $builder->whereAny( ['data'], 'like', '%' . $filter['any'] . '%' );
                    }
                });
            });
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

        switch( $args['publish'] ?? null )
        {
            case 'PUBLISHED':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_pages.id' )
                        ->where( 'cms_versions.versionable_type', Page::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->havingRaw('published = 1 OR published IS NULL');
                break;
            case 'DRAFT':
                $builder->addSelect( [
                    'published' => DB::table('cms_versions')
                        ->select( 'published' )
                        ->whereColumn( 'cms_versions.versionable_id', 'cms_pages.id' )
                        ->where( 'cms_versions.versionable_type', Page::class )
                        ->orderByDesc( 'id' )
                        ->limit( 1 )
                ] )->groupBy( 'id' )->having( 'published', false );
                break;
            case 'SCHEDULED':
                $builder->whereHas('versions', function( $query ) {
                    $query->where( 'versionable_type', Page::class )
                        ->where( 'publish_at', '!=', null )
                        ->where( 'published', false );
                });
                break;
        }

        if( !empty( $filter ) )
        {
            $builder->where( function( $builder ) use ( $filter ) {

                if( array_key_exists( 'parent_id', $filter ) ) {
                    $builder->where( 'parent_id', $filter['parent_id'] );
                }

                if( array_key_exists( 'id', $filter ) ) {
                    $builder->whereIn( 'id', $filter['id'] );
                }

                if( isset( $filter['lang'] ) ) {
                    $builder->where( 'lang', (string) $filter['lang'] );
                }

                if( array_key_exists( 'to', $filter ) ) {
                    $builder->where( 'to', (string) $filter['to'] );
                }

                if( array_key_exists( 'path', $filter ) ) {
                    $builder->where( 'path', (string) $filter['path'] );
                }

                if( array_key_exists( 'domain', $filter ) ) {
                    $builder->where( 'domain', (string) $filter['domain'] );
                }

                if( array_key_exists( 'tag', $filter ) ) {
                    $builder->where( 'tag', (string) $filter['tag'] );
                }

                if( array_key_exists( 'theme', $filter ) ) {
                    $builder->where( 'theme', (string) $filter['theme'] );
                }

                if( array_key_exists( 'type', $filter ) ) {
                    $builder->where( 'type', (string) $filter['type'] );
                }

                if( array_key_exists( 'status', $filter ) ) {
                    $builder->where( 'status', (int) $filter['status'] );
                }

                if( array_key_exists( 'cache', $filter ) ) {
                    $builder->where( 'cache', (int) $filter['cache'] );
                }

                if( array_key_exists( 'name', $filter ) ) {
                    $builder->where( 'name', 'like', $filter['name'] . '%' );
                }

                if( array_key_exists( 'title', $filter ) ) {
                    $builder->where( 'title', 'like', $filter['title'] . '%' );
                }

                if( array_key_exists( 'editor', $filter ) ) {
                    $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                }

                if( array_key_exists( 'meta', $filter ) ) {
                    $builder->where( 'meta', 'like', '%' . $filter['meta'] . '%' );
                }

                if( array_key_exists( 'config', $filter ) ) {
                    $builder->where( 'config', 'like', '%' . $filter['config'] . '%' );
                }

                if( array_key_exists( 'content', $filter ) ) {
                    $builder->where( 'content', 'like', '%' . $filter['content'] . '%' );
                }

                if( array_key_exists( 'any', $filter ) ) {
                    $builder->whereAny( ['config', 'content', 'meta', 'name', 'title'], 'like', '%' . $filter['any'] . '%' );
                }

                $builder->orWhereHas('versions', function( $builder ) use ( $filter ) {

                    if( array_key_exists( 'parent_id', $filter ) ) {
                        $builder->where( 'cms_pages.parent_id', $filter['parent_id'] );
                    }

                    if( array_key_exists( 'id', $filter ) ) {
                        $builder->whereIn( 'versionable_id', $filter['id'] );
                    }

                    if( isset( $filter['lang'] ) ) {
                        $builder->where( 'lang', (string) $filter['lang'] );
                    }

                    if( array_key_exists( 'editor', $filter ) ) {
                        $builder->where( 'editor', 'like', $filter['editor'] . '%' );
                    }

                    if( array_key_exists( 'to', $filter ) ) {
                        $builder->where( 'data->to', (string) $filter['to'] );
                    }

                    if( array_key_exists( 'path', $filter ) ) {
                        $builder->where( 'data->path', (string) $filter['path'] );
                    }

                    if( array_key_exists( 'domain', $filter ) ) {
                        $builder->where( 'data->domain', (string) $filter['domain'] );
                    }

                    if( array_key_exists( 'tag', $filter ) ) {
                        $builder->where( 'data->tag', (string) $filter['tag'] );
                    }

                    if( array_key_exists( 'theme', $filter ) ) {
                        $builder->where( 'data->theme', (string) $filter['theme'] );
                    }

                    if( array_key_exists( 'type', $filter ) ) {
                        $builder->where( 'data->type', (string) $filter['type'] );
                    }

                    if( array_key_exists( 'status', $filter ) ) {
                        $builder->where( 'data->status', (int) $filter['status'] );
                    }

                    if( array_key_exists( 'cache', $filter ) ) {
                        $builder->where( 'data->cache', (int) $filter['cache'] );
                    }

                    if( array_key_exists( 'name', $filter ) ) {
                        $builder->where( 'data->name', 'like', $filter['name'] . '%' );
                    }

                    if( array_key_exists( 'title', $filter ) ) {
                        $builder->where( 'data->title', 'like', $filter['title'] . '%' );
                    }

                    if( array_key_exists( 'meta', $filter ) ) {
                        $builder->where( 'aux->meta', 'like', '%' . $filter['meta'] . '%' );
                    }

                    if( array_key_exists( 'config', $filter ) ) {
                        $builder->where( 'aux->config', 'like', '%' . $filter['config'] . '%' );
                    }

                    if( array_key_exists( 'content', $filter ) ) {
                        $builder->where( 'aux->content', 'like', '%' . $filter['content'] . '%' );
                    }

                    if( array_key_exists( 'any', $filter ) ) {
                        $builder->whereAny( ['aux', 'data'], 'like', '%' . $filter['any'] . '%' );
                    }
                } );
            } );
        }

        return $builder;
    }
}
