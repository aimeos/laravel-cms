<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\File;


final class KeepFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        $file = File::withTrashed()->findOrFail( $args['id'] );
        $file->editor = Auth::user()?->name ?? request()->ip();

        $file->restore();
        return $file;
    }
}
