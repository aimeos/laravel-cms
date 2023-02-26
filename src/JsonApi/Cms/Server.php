<?php

namespace Aimeos\Cms\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;


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
        // no-op
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
