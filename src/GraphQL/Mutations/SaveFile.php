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
        $editor = Auth::user()?->name ?? request()->ip();
        $orig = File::withTrashed()->findOrFail( $args['id'] );

        $file = clone $orig;
        $file->fill( $args['input'] ?? [] );
        $file->editor = $editor;

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

        $file->versions()->create( [
            'lang' => $args['input']['lang'] ?? null,
            'editor' => $editor,
            'data' => [
                'lang' => $file->lang,
                'name' => $file->name,
                'mime' => $file->mime,
                'path' => $file->path,
                'previews' => $file->previews,
                'description' => $file->description,
            ],
        ] );

        $file->removeVersions();

        return $orig;
    }
}
