<?php

namespace Aimeos\Cms\JsonApi\V1;

use Illuminate\Support\Facades\Url;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LaravelJsonApi\Core\Server\Server as BaseServer;
use LaravelJsonApi\Core\Document\JsonApi;


class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri;


    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        if( !\Aimeos\Cms\Permission::can( 'page:view', request()->user() ) ) {
            \Aimeos\Cms\Models\Page::addGlobalScope( new \Aimeos\Cms\Scopes\Status() );
        }
    }


    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [Pages\PageSchema::class];
    }


    /**
     * Returns the base URL for generated links in the JSON API response.
     *
     * @return string Base URL
     */
    protected function baseUri(): string
    {
        if( !isset( $this->baseUri ) ) {
            $this->baseUri = Route::has( 'cms.pages' ) ? str_replace( '/pages', '', Url::route( 'cms.pages' ) ) : '/cms';
        }

        return $this->baseUri;
    }
}
