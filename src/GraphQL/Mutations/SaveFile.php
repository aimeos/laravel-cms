<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Aimeos\Cms\Models\File;


final class SaveFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        $orig = File::withTrashed()->findOrFail( $args['id'] );
        $file = clone $orig;
        $file->fill( $args['input'] ?? [] );

        $upload = $args['file'] ?? null;

        if( $upload instanceof UploadedFile ) {
            $file->addFile( $upload );
        }

        try
        {
            $preview = $args['preview'] ?? null;

            if( $preview instanceof UploadedFile ) {
                $file->addPreviews( $preview );
            } elseif( $upload instanceof UploadedFile ) {
                $file->addPreviews( $upload );
            } elseif( $preview === false ) {
                $file->previews = [];
            }
        }
        catch( \Throwable $t )
        {
            $file->removePreviews()->removeFile();
            throw $t;
        }

        $editor = Auth::user()?->name ?? request()->ip();
        $file->versions()->create( [
            'editor' => $editor,
            'data' => [
                'tag' => $file->tag,
                'name' => $file->name,
                'mime' => $file->mime,
                'path' => $file->path,
                'previews' => $file->previews,
                'description' => $file->description,
                'editor' => $editor,
            ],
        ] );

        $file->removeVersions();

        return $orig;
    }
}
