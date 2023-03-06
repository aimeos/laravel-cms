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
        $content->fill( $args['input'] ?? [] );
        $content->editor = Auth::user()?->name ?? request()->ip();
        $content->save();

        $content->files()->syncWithPivotValues( $args['files'] ?? [], ['tenant_id' => \Aimeos\Cms\Tenancy::value()] );

        return $content;
    }
}
