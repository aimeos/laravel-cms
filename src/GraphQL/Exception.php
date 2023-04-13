<?php

namespace Aimeos\Cms\GraphQL;

use GraphQL\Error\ClientAware;


final class Exception extends \Exception implements ClientAware
{
    private $reason;


    public function __construct( string $message, string $reason = null )
    {
        parent::__construct( $message );
        $this->reason = $reason;
    }


    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }


    /**
     * Data to include within the "extensions" key of the formatted error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return ['reason' => $this->reason];
    }
}
