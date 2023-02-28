<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
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

        $dir = 'cms/' . \Aimeos\Cms\Tenancy::value();
        $path = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( $dir, $upload, 'public' );
        $previews = [];

        foreach( $args['previews'] ?? [] as $idx => $preview ) {
            $previews[] = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( $dir, $preview, 'public' );
        }

        $file = File::forceCreate( [
            'name' => $args['name'] ?? pathinfo( $upload->getClientOriginalName(), PATHINFO_BASENAME ),
            'mime' => $upload->getClientMimeType(),
            'path' => $path,
            'previews' => $previews,
        ] );

        $file->editor = Auth::user()?->name ?? request()->ip();
        $file->save();

        return $file;
    }
}
