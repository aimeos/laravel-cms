<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Ref;


final class DropRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $ref = Ref::findOrFail( $args['id'] ?? null );
        $ref->delete();

        $page = Page::findOrFail( $ref->page_id );
        Cache::forget( Page::key( $page->slug, $page->lang ) );

        return $ref;
    }
}
