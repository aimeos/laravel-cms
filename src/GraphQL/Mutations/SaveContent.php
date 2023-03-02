<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class SaveContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = Content::findOrFail( $args['id'] );
        $content->editor = Auth::user()?->name ?? request()->ip();

        $content->fill( $args['input'] ?? [] )->save();
        return $content;
    }
}
