<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Ref;


final class SaveRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $editor = Auth::user()?->name ?? request()->ip();

        $ref = Ref::findOrFail( $args['id'] );
        $ref->fill( $args['input'] ?? [] );
        $ref->editor = $editor;
        $ref->save();

        $page = Page::findOrFail( $ref->page_id );
        Cache::forget( Page::key( $page->slug, $page->lang ) );

        return $ref;
    }
}
