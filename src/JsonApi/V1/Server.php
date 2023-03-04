<?php

namespace Aimeos\Cms\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;
use Aimeos\Cms\Scopes\Status;


class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/cms';


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
