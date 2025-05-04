<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Aimeos\Cms\Models\File;
use Aimeos\Cms\GraphQL\Exception;
use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

        $disk = Storage::disk( config( 'cms.disk', 'public' ) );
        $dir = rtrim( 'cms/' . \Aimeos\Cms\Tenancy::value(), '/' );

        try
        {
            $filename = $upload->getClientOriginalName();
            $ext = preg_replace( '/[[:cntrl:]]|[[:blank]]|\/|\./smu', '', pathinfo( $filename, PATHINFO_EXTENSION ) );
            $path = $dir . '/' . $this->name( $upload ) . '.' . $ext;

            if( !$disk->putFileAs( $dir, $upload, $this->name( $upload ) . '.' . $ext ) ) {
                throw new \RuntimeException( sprintf( 'Unable to store file "%s" to "%s"', $filename, $path ) );
            }

            $previews = $this->previews( $dir, $upload, $args['previews'] ?? [] );

            $file = new File();
            $file->name = $args['input']['name'] ?? pathinfo( $upload->getClientOriginalName(), PATHINFO_BASENAME );
            $file->tag = $args['input']['tag'] ?? '';
            $file->mime = $upload->getClientMimeType();
            $file->path = $path;
            $file->previews = (object) $previews;
            $file->editor = Auth::user()?->name ?? request()->ip();
            $file->save();

            return $file;
        }
        catch( \Throwable $t )
        {
            foreach( $previews ?? [] as $preview ) {
                $disk->delete( $preview );
            }
            $disk->delete( $path );

            throw $t;
        }

        return null;
    }


    /**
     * Returns the new name for the uploaded file
     *
     * @param UploadedFile $file Uploaded file
     * @param array $size Image width and height if used
     * @return string New file name
     */
    protected function name( UploadedFile $file, array $size = [] ) : string
    {
        $filename = $file->getClientOriginalName();
        $name = preg_replace( '/[[:cntrl:]]|[[:blank]]|\/|\./smu', '', pathinfo( $filename, PATHINFO_FILENAME ) );
        $hash = substr( md5( microtime(true) . getmypid() . rand(0, 1000) ), -4 );

        return $name . '_' . ( $size['width'] ?? $size['height'] ?? '' ) . '_' . $hash;
    }


    /**
     * Creates the preview images
     *
     * @param string $dir Relative directory path
     * @param UploadedFile $upload File upload
     * @param array $previews List of UploadedFile for preview files
     * @return array List of preview image paths with image widths as keys
     */
    protected function previews( string $dir, UploadedFile $upload, array $previews ) : array
    {
        $map = [];
        $sizes = config( 'cms.image.preview-sizes', [[]] );
        $disk = Storage::disk( config( 'cms.disk', 'public' ) );

        $driver = ucFirst( config( 'cms.image.driver', 'gd' ) );
        $manager = ImageManager::withDriver( '\\Intervention\\Image\\Drivers\\' . $driver . '\Driver' );
        $ext = $manager->driver()->supports( 'image/webp' ) ? 'webp' : 'jpg';

        if( count( $previews ) > 1 )
        {
            foreach( $previews as $preview )
            {
                if( str_starts_with( $preview->getClientMimeType(), 'image/' ) )
                {
                    $image = $manager->read( $preview );
                    $path = $dir . '/' . $this->name( $preview ) . '.' . $ext;

                    if( !isset( $map[$image->width()] ) && $disk->put( $path, $image->encodeByExtension( $ext )->toFilePointer(), 'public' ) ) {
                        $map[$image->width()] = $path;
                    }
                }
            }
        }
        elseif( isset( $previews[0] ) && str_starts_with( $preview[0]->getClientMimeType(), 'image/' ) )
        {
            $file = $manager->read( $preview[0] );

            foreach( $sizes as $size )
            {
                $path = $dir . '/' . $this->name( $preview[0], $size ) . '.' . $ext;
                $image = ( clone $file )->scaleDown( $size['width'] ?? null, $size['height'] ?? null );

                if( $disk->put( $path, $image->encodeByExtension( $ext )->toFilePointer(), 'public' ) ) {
                    $map[$image->width()] = $path;
                }
            }
        }
        elseif( str_starts_with( $upload->getClientMimeType(), 'image/' ) )
        {
            $file = $manager->read( $upload );

            foreach( $sizes as $size )
            {
                $path = $dir . '/' . $this->name( $upload, $size ) . '.' . $ext;
                $image = ( clone $file )->scaleDown( $size['width'] ?? null, $size['height'] ?? null );

                if( $disk->put( $path, $image->encodeByExtension( $ext )->toFilePointer(), 'public' ) ) {
                    $map[$image->width()] = $path;
                }
            }
        }

        return $map;
    }
}
