<?php

namespace Aimeos\Cms\JsonApi\V1;

use Illuminate\Support\Facades\Storage;
use LaravelJsonApi\Core\Server\Server as BaseServer;
use LaravelJsonApi\Core\Document\JsonApi;
use Aimeos\Cms\Scopes\Status;


class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/cms';


    public function jsonApi(): JsonApi
    {
      return JsonApi::make( '1.0' )->setMeta( [
        'baseurl' => Storage::url( '' )
      ] );
    }


    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        \Aimeos\Cms\Models\Page::addGlobalScope( new Status() );
    }


    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            Contents\ContentSchema::class,
            Pages\PageSchema::class,
        ];
    }
}
