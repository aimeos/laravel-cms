<?php

namespace Aimeos\Cms\Actions;

use Aimeos\Cms\Permission;
use Aimeos\Cms\Models\Page;
use Illuminate\Http\Request;


class Pages
{
    public function list( Request $request, Page $page, object $item )
    {
        $pid = @$item->data?->{'parent-page'}?->value ?: $page->id;
        $sort = @$item->data?->order ?: '-id';

        $order = $sort[0] === '-' ? substr( $sort, 1 ) : $sort;
        $dir = $sort[0] === '-' ? 'desc' : 'asc';

        $builder = Page::where( 'parent_id', $pid )->where( 'type', 'blog' )->orderBy( $order, $dir );

        if( !Permission::can( 'page:view', $request->user() ) ) {
            $builder->where( 'status', '>', 0 );
        }

        return $builder->paginate( @$item->data?->limit ?? 10 );
    }
}
