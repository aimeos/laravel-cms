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
        $file->path = $args['input']['path'] ?? $orig->path;
        $file->editor = $editor;

        $upload = $args['file'] ?? null;

        if( $upload instanceof UploadedFile && $upload->isValid() ) {
            $file->addFile( $upload );
        }

        try
        {
            $file->previews = [];
            $preview = $args['preview'] ?? null;

            if( $preview instanceof UploadedFile && $preview->isValid() && str_starts_with( $preview->getClientMimeType(), 'image/' ) ) {
                $file->addPreviews( $preview );
            } elseif( $upload instanceof UploadedFile && $upload->isValid() && str_starts_with( $upload->getClientMimeType(), 'image/' ) ) {
                $file->addPreviews( $upload );
            } elseif( $file->path !== $orig->path && str_starts_with( $file->path, 'http' ) ) {
                $file->addPreviews( $file->path );
            } elseif( $preview === false ) {
                $file->previews = [];
            } else {
                $file->previews = $orig->previews;
            }
        }
        catch( \Throwable $t )
        {
            $file->removePreviews();
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
