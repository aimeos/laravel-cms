<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class AddContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $editor = Auth::user()?->name ?? request()->ip();

        $content = Content::create( $args['input'] ?? [] );
        $content->editor = $editor;
        $content->save();

        if( $args['page_id'] ?? null )
        {
            $ref = Ref::create( [
                'page_id', $args['page_id'],
                'content_id', $content->id,
                'position', $args['position'] ?? 0,
            ] );
            $ref->editor = $editor;
            $ref->save();
        }

        return $content;
    }
}
