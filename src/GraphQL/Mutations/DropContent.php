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
        $content->editor = Auth::user()?->email ?? request()->ip();

        $content->delete();
        return $content;
    }
}
