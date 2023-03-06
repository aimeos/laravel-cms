<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aimeos\Cms\Models\File;


final class AddFile
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
        $previews = [];

        try
        {
            foreach( $args['previews'] ?? [] as $idx => $preview )
            {
                if( str_starts_with( $preview->getClientMimeType(), 'image/' ) )
                {
                    $width = Image::make( $preview )->width();
                    $previews[$width] = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( $dir, $preview, 'public' );
                }
            }

            $path = Storage::disk( config( 'cms.disk', 'public' ) )->putFile( $dir, $upload, 'public' );

            $file = new File();
            $file->name = $args['input']['name'] ?? pathinfo( $upload->getClientOriginalName(), PATHINFO_BASENAME );
            $file->tag = $args['input']['tag'] ?? '';
            $file->mime = $upload->getClientMimeType();
            $file->path = $path;
            $file->previews = $previews;
            $file->editor = Auth::user()?->name ?? request()->ip();
            $file->save();

            return $file;
        }
        catch( \Exception $e )
        {
            foreach( $previews as $preview ) {
                Storage::disk( config( 'cms.disk', 'public' ) )->delete( $preview );
            }

            if( isset( $path ) ) {
                Storage::disk( config( 'cms.disk', 'public' ) )->delete( $path );
            }

            throw $e;
        }

        return null;
    }
}
