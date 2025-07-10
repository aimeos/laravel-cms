<?php

namespace Aimeos\Cms\Actions;

use Aimeos\Cms\Permission;
use Aimeos\Cms\Models\Page;
use Illuminate\Http\Request;


class Blog
{
    public function __invoke( Request $request, Page $page, object $item )
    {
        $pid = @$item->data?->{'parent-page'}?->value ?: $page->id;
        $sort = @$item->data?->order ?: '-id';

        $order = $sort[0] === '-' ? substr( $sort, 1 ) : $sort;
        $dir = $sort[0] === '-' ? 'desc' : 'asc';

        $builder = Page::where( 'parent_id', $pid )
            ->where( function( $builder ) use ( $request ) {
                $builder->where( 'type', 'blog' );

                if( Permission::can( 'page:view', $request->user() ) ) {
                    $builder->orWhereHas( 'versions', function( $builder ) {
                        $builder->where( 'data->type', 'blog' );
                    } );
                } else {
                    $builder->where( 'status', '>', 0 );
                }
            } )->orderBy( $order, $dir );

        $attr = ['id', 'lang', 'path', 'name', 'title', 'to', 'domain', 'content'];

        return $builder->paginate( @$item->data?->limit ?? 10, $attr, 'p' )
            ->through( function( $item ) {
                $lang = $item->lang;
                $item->content = collect( $item->content )->filter( fn( $item ) => $item->type === 'article' );
                $files = collect( $item->content )
                    ->map( fn( $item ) => $item->files )
                    ->collapse()
                    ->unique()
                    ->map( fn( $id ) => $item->files[$id] ?? null )
                    ->filter()
                    ->pluck( null, 'id' )
                    ->each( fn( $file ) => $file->description = $file->description?->{$lang}
                        ?? $file->description?->{substr( $lang, 0, 2 )}
                        ?? null
                    );

                return $item->setRelation( 'files', $files );
            } );
    }
}
