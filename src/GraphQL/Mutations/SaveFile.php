<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\File;


final class SaveFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        $file = File::withTrashed()->findOrFail( $args['id'] );
        $file->editor = Auth::user()?->name ?? request()->ip();

        $file->fill( $args['input'] ?? [] )->save();
        return $file;
    }
}
