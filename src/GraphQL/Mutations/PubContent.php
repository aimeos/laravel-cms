<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Aimeos\Cms\Models\Content;


final class PubContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = Content::findOrFail( $args['id'] );

        $content->data = $content->latest?->data ?: $content->data;
        $content->editor = Auth::user()?->name ?? request()->ip();

        $content->save();

        return $content;
    }
}
