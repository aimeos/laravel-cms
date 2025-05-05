<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Aimeos\Cms\Models\File;
use Aimeos\Cms\GraphQL\Exception;
use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


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
            $ext = preg_replace( '/[[:cntrl:]]|[[:blank:]]|\/|\./smu', '', pathinfo( $filename, PATHINFO_EXTENSION ) );
            $path = $dir . '/' . $this->name( $upload ) . '.' . $ext;

            if( !$disk->putFileAs( $dir, $upload, $this->name( $upload ) . '.' . $ext ) ) {
                throw new \RuntimeException( sprintf( 'Unable to store file "%s" to "%s"', $filename, $path ) );
            }

            $previews = $this->previews( $dir, $upload, $args['preview'] ?? null );

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
        $name = preg_replace( '/[[:cntrl:]]|[[:blank:]]|\/|\./smu', '', pathinfo( $filename, PATHINFO_FILENAME ) );
        $hash = substr( md5( microtime(true) . getmypid() . rand(0, 1000) ), -4 );

        return $name . '_' . ( $size['width'] ?? $size['height'] ?? '' ) . '_' . $hash;
    }


    /**
     * Creates the preview images
     *
     * @param string $dir Relative directory path
     * @param UploadedFile $upload File upload
     * @param UploadedFile|null $preview UploadedFile for preview file
     * @return array List of preview image paths with image widths as keys
     */
    protected function previews( string $dir, UploadedFile $upload, ?UploadedFile $preview = null ) : array
    {
        $map = [];
        $sizes = config( 'cms.image.preview-sizes', [[]] );
        $disk = Storage::disk( config( 'cms.disk', 'public' ) );

        $driver = ucFirst( config( 'cms.image.driver', 'gd' ) );
        $manager = ImageManager::withDriver( '\\Intervention\\Image\\Drivers\\' . $driver . '\Driver' );
        $ext = $manager->driver()->supports( 'image/webp' ) ? 'webp' : 'jpg';

        if( $preview && str_starts_with( $preview->getClientMimeType(), 'image/' ) )
        {
            $file = $manager->read( $preview );

            foreach( $sizes as $size )
            {
                $image = ( clone $file )->scaleDown( $size['width'] ?? null, $size['height'] ?? null );
                $path = $dir . '/' . $this->name( $preview, $size ) . '.' . $ext;
                $ptr = $image->encodeByExtension( $ext )->toFilePointer();

                if( $disk->put( $path, $ptr, 'public' ) ) {
                    $map[$image->width()] = $path;
                }
            }
        }
        elseif( str_starts_with( $upload->getClientMimeType(), 'image/' ) )
        {
            $file = $manager->read( $upload );

            foreach( $sizes as $size )
            {
                $image = ( clone $file )->scaleDown( $size['width'] ?? null, $size['height'] ?? null );
                $path = $dir . '/' . $this->name( $upload, $size ) . '.' . $ext;
                $ptr = $image->encodeByExtension( $ext )->toFilePointer();

                if( $disk->put( $path, $ptr, 'public' ) ) {
                    $map[$image->width()] = $path;
                }
            }
        }

        return $map;
    }
}
