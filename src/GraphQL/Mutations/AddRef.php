<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Ref;


final class AddRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $page = Page::findOrFail( $args['page_id'] ?? null );

        $ref = Ref::create( [
            'page_id', $page->id,
            'content_id', $args['content_id'] ?? null,
            'position', $args['position'] ?? 0,
        ] );
        $ref->editor = Auth::user()?->name ?? request()->ip();
        $ref->tenancy_id = \Aimeos\Cms\Tenancy::value();

        return $ref;
    }
}
