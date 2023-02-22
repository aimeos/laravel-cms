<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aimeos\Cms\Models\File;


final class UploadFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : ?File
    {
        if( empty( $upload = $args['file'] ?? null ) ) {
            return null;
        }

        $path = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( 'files', $upload, 'public' );

        // preserve dot for file extension, e.g. ".jpg"
        $name = Str::replaceLast( '_', '.', Str::slug( Str::replace( '.', '_', basename( $upload->getClientOriginalName() ) ), '_' ) );

        $file = File::forceCreate( [
            'mime' => $upload->getClientMimeType(),
            'name' => $name,
            'url' => $path,
        ] );

        $file->save();
        return $file;
    }
}
