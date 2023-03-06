<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\Ref;


final class AddContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $editor = Auth::user()?->name ?? request()->ip();

        $content = new Content();
        $content->fill( $args['input'] ?? [] );
        $content->tenant_id = \Aimeos\Cms\Tenancy::value();
        $content->editor = $editor;
        $content->save();

        if( $pageId = $args['page_id'] ?? null )
        {
            $ref = new Ref();
            $ref->page_id = $pageId;
            $ref->content_id = $content->id;
            $ref->position = $args['position'] ?? 0;
            $ref->tenant_id = \Aimeos\Cms\Tenancy::value();
            $ref->editor = $editor;
            $ref->save();
        }

        return $content;
    }
}
