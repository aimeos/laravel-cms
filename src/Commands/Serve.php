<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\ServeCommand;


class Serve extends ServeCommand
{
    /**
     * Command name
     */
    protected $signature = 'cms:serve';

    /**
     * Command description
     */
    protected $description = 'Serve the application for CMS development';


    protected function serverCommand()
    {
        return [
            php_binary(),
            '-S',
            $this->host().':'.$this->port(),
            '-d',
            'upload_max_filesize=100M',
            '-d',
            'post_max_size=100M',
            __DIR__.'/../server.php',
        ];
    }
}
