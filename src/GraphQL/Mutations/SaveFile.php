<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
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

        if( !empty( $preview = $args['preview'] ) && str_starts_with( $preview->getClientMimeType(), 'image/' ) ) {
            $file->previews = (object) $this->previews( $preview );
        }

        $file->fill( $args['input'] ?? [] )->save();
        return $file;
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
     * @param UploadedFile $preview Preview file upload
     * @return array List of preview image paths with image widths as keys
     */
    protected function previews( UploadedFile $preview ) : array
    {
        $map = [];
        $sizes = config( 'cms.image.preview-sizes', [[]] );
        $disk = Storage::disk( config( 'cms.disk', 'public' ) );
        $dir = rtrim( 'cms/' . \Aimeos\Cms\Tenancy::value(), '/' );

        $driver = ucFirst( config( 'cms.image.driver', 'gd' ) );
        $manager = ImageManager::withDriver( '\\Intervention\\Image\\Drivers\\' . $driver . '\Driver' );
        $ext = $manager->driver()->supports( 'image/webp' ) ? 'webp' : 'jpg';

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

        return $map;
    }
}
