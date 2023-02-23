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

        $path = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( 'cms', $upload, 'public' );
        $previews = [];

        foreach( $args['previews'] ?? [] as $idx => $preview ) {
            $previews[] = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( 'cms', $preview, 'public' );
        }

        // preserve dot for file extension, e.g. ".jpg"
        $name = Str::replaceLast( '_', '.', Str::slug( Str::replace( '.', '_', basename( $upload->getClientOriginalName() ) ), '_' ) );

        $file = File::forceCreate( [
            'mime' => $upload->getClientMimeType(),
            'name' => $name,
            'path' => $path,
            'previews' => $previews,
        ] );

        $file->save();
        return $file;
    }
}
