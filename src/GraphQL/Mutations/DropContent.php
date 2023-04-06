<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class DropContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = Content::withTrashed()->findOrFail( $args['id'] );
        $content->editor = Auth::user()?->name ?? request()->ip();

        $force = $args['force'] ?? false;
        $force ? $content->forceDelete() : $content->delete();

        return $content;
    }
}
