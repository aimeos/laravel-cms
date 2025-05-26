<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Aimeos\Cms\Models\File;
use Aimeos\Cms\GraphQL\Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;


final class AddFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        if( empty( $upload = $args['file'] ?? null ) || !$upload instanceof UploadedFile || !$upload->isValid() ) {
            throw new Exception( 'Invalid file upload' );
        }

        $file = new File();
        $file->fill( $args['input'] ?? [] );
        $file->editor = Auth::user()?->name ?? request()->ip();
        $file->name = $file->name ?: pathinfo( $upload->getClientOriginalName(), PATHINFO_BASENAME );
        $file->mime = $upload->getClientMimeType();

        try
        {
            $file->addFile( $upload )->addPreviews( $args['preview'] ?? $upload );
            $file->save();
        }
        catch( \Throwable $t )
        {
            $file->removePreviews()->removeFile();
            throw $t;
        }

        return $file;
    }
}
