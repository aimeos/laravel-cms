<?php

namespace Aimeos\Cms\Actions;

use Aimeos\Cms\Permission;
use Aimeos\Cms\Models\Page;
use Illuminate\Http\Request;


class Blog
{
    public function __invoke( Request $request, Page $model, object $item )
    {
        $pid = @$item->data?->{'parent-page'}?->value ?: $model->id;
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

        $pageattr = ['id', 'lang', 'path', 'name', 'title', 'to', 'domain', 'content'];
        $fileattr = ['id', 'lang', 'name', 'mime', 'path', 'previews', 'description'];

        return $builder->paginate( @$item->data?->limit ?? 10, $pageattr, 'p' )
            ->through( function( $item ) use ( $fileattr ) {
                $item->content = collect( $item->content )->filter( fn( $item ) => $item->type === 'article' );
                $fileIds = collect( $item->content )->map( fn( $item ) => $item->files )->collapse()->unique();

                return $item->setRelation( 'files', $item->files->map( function( $file ) use ( $fileattr, $fileIds ) {
                    return $fileIds->contains( $file->id ) ? collect( $file->toArray() )->only( $fileattr )->all() : [];
                } ) );
            } );
    }
}
