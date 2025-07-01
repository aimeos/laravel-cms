<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Aimeos\Cms\Models\File;
use Aimeos\Cms\GraphQL\Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


final class AddFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        if( empty( $args['input']['path'] ) && empty( $args['file'] ) ) {
            throw new Exception( 'Either input "path" or "file" argument must be provided' );
        }

        $file = new File();
        $file->fill( $args['input'] ?? [] );
        $file->editor = Auth::user()?->name ?? request()->ip();

        if( isset( $args['file'] ) ) {
            $this->addUpload( $file, $args );
        } else {
            $this->addUrl( $file, $args );
        }

        $file->save();
        return $file;
    }


    /**
     * Adds the uploaded file to the file model.
     *
     * @param  File $file File model instance
     * @param  array $args Arguments containing the file upload
     * @return File The updated file model instance
     */
    protected function addUpload( File $file, array $args ) : File
    {
        $upload = $args['file'] ?? null;

        if( !$upload instanceof UploadedFile || !$upload->isValid() ) {
            throw new Exception( 'Invalid file upload' );
        }

        $file->mime = $upload->getClientMimeType();
        $file->name = $file->name ?: pathinfo( $upload->getClientOriginalName(), PATHINFO_BASENAME );

        try
        {
            $file->addFile( $upload );

            if( isset( $args['preview'] ) || str_starts_with( $upload->getClientMimeType(), 'image/' ) ) {
                $file->addPreviews( $args['preview'] ?? $upload );
            }
        }
        catch( \Throwable $t )
        {
            $file->removePreviews()->removeFile();
            throw $t;
        }

        return $file;
    }


    /**
     * Adds a file from a URL to the file model.
     *
     * @param  File $file File model instance
     * @param  array $args Arguments containing the URL
     * @return File The updated file model instance
     */
    protected function addUrl( File $file, array $args ) : File
    {
        $url = $args['input']['path'] ?? null;

        if( ( $info = $this->getUrlInfo( $url ) ) === null ) {
            throw new Exception( 'Invalid URL' );
        }

        $file->path = $url;
        $file->name = $file->name ?: $info['name'];
        $file->mime = $info['mime'] ?? 'application/octet-stream';

        try
        {
            if( isset( $args['preview'] ) || !str_starts_with( $file->mime, 'image/' ) ) {
                $file->addPreviews( $args['preview'] ?? $url );
            }
        }
        catch( \Throwable $t )
        {
            $file->removePreviews();
            throw $t;
        }

        return $file;
    }


    /**
     * Validates if the given URL and returns the MIME type.
     *
     * @param  string $url URL to check
     * @return array|null Array with mime type and file name or NULL if the URL is invalid or not accessible
     */
    protected function getUrlInfo( string $url ) : ?array
    {
        if( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return null;
        }

        $response = Http::head( $url );

        if( !$response->successful() || $response->status() >= 300 ) {
            return null;
        }

        $str = $response->header( 'Content-Disposition' );
        $matches = [];
        $name = null;

        if( $str && preg_match( '/filename="?([^"]+)"?/', $str, $matches ) ) {
            $name = $matches[1];
        }

        return [
            'mime' => $response->header( 'Content-Type' ),
            'name' => $name,
        ];
    }
}
