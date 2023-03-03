<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Commands;

use Illuminate\Console\Command;


class Install extends Command
{
	private static $template = '<fg=blue>
    __                                __________  ________
   / /   ____ __________ __   _____  / / ____/  |/  / ___/
  / /   / __ `/ ___/ __ `/ | / / _ \/ / /   / /|_/ /\__ \
 / /___/ /_/ / /  / /_/ /| |/ /  __/ / /___/ /  / /___/ /
/_____/\__,_/_/   \__,_/ |___/\___/_/\____/_/  /_//____/
</>
Congratulations! You successfully set up <fg=green>LaravelCMS</>!
<fg=cyan>Give a star and contribute</>: https://github.com/aimeos/laravel-cms
Made with <fg=green>love</> by the LaravelCMS community. Be a part of it!
';


    /**
     * Command name
     */
    protected $signature = 'cms:install  {--seed : Add example pages to the database}';

    /**
     * Command description
     */
    protected $description = 'Installing Laravel CMS package';


    /**
     * Execute command
     */
    public function handle()
    {
        $result = 0;

        $this->comment( '  Publishing CMS files ...' );
        $result += $this->call( 'vendor:publish', ['--tag' => 'cms'] );

        $this->comment( '  Publishing JSON:API configuration ...' );
        $result += $this->call( 'vendor:publish', ['--provider' => 'LaravelJsonApi\Laravel\ServiceProvider'] );

        $this->comment( '  Updating JSON:API configuration ...' );
        $result += $this->jsonapi();

        $this->comment( '  Adding JSON:API exception handler ...' );
        $result += $this->exception();

        $this->comment( '  Publishing Lighthouse schema ...' );
        $result += $this->call( 'vendor:publish', ['--tag' => 'lighthouse-schema'] );

        $this->comment( '  Publishing Lighthouse configuration ...' );
        $result += $this->call( 'vendor:publish', ['--tag' => 'lighthouse-config'] );

        $this->comment( '  Updating Lighthouse configuration ...' );
        $result += $this->lighthouse();

        $this->comment( '  Adding Laravel CMS GraphQL schema ...' );
        $result += $this->schema();

        $this->comment( '  Creating database ...' );
        $result += $this->db();

        $this->comment( '  Migrating database ...' );
        $result += $this->call( 'migrate' );

        if( $this->option( 'seed' ) )
        {
            $this->comment( '  Seed database ...' );
            $result += $this->call( 'db:seed', ['--class' => 'CmsSeeder'] );
        }

        $this->comment( '  Adding Laravel CMS routes ...' );
        $result += $this->route();

        $this->comment( '  Link public storage folder ...' );
        $result += $this->call( 'storage:link', ['--force' => null] );

        if( $result ) {
            $this->error( '  Error during Laravel CMS installation!' );
        } else {
            $this->line( self::$template );
        }
    }


    /**
     * Creates the database if necessary
     *
     * @return int 0 on success, 1 on failure
     */
    protected function db() : int
    {
        $path = env('DB_DATABASE', database_path( 'database.sqlite' ) );

        if( config( 'cms.db', 'sqlite' ) && !file_exists( $path ) )
        {
            if( touch( $path ) === true ) {
                $this->line( sprintf( '  Created database [%1$s]' . PHP_EOL, $path ) );
            } else {
                $this->error( sprintf( '  Creating database [%1$s] failed!' . PHP_EOL, $path ) ); exit( 1 );
            }
        }
        else
        {
            $this->line( '  Creating database is not necessary' . PHP_EOL );
        }

        return 0;
    }


    /**
     * Updates application exception handler
     *
     * @return int 0 on success, 1 on failure
     */
    protected function exception() : int
    {
        $done = 0;
        $filename = 'app/Exceptions/Handler.php';
        $content = file_get_contents( base_path( $filename ) );

        $search = 'public function register()
    {';

        $string = '
        $this->renderable(
            \LaravelJsonApi\Exceptions\ExceptionParser::make()->renderable()
        );
        ';

        if( strpos( $content, '\LaravelJsonApi\Exceptions\ExceptionParser' ) === false && ++$done )
        {
            $content = str_replace( $search, $search . $string, $content );
            $this->line( sprintf( '  Added JSON:API exception handler to [%1$s]' . PHP_EOL, $filename ) );
        }

        if( $done ) {
            file_put_contents( base_path( $filename ), $content );
        } else {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }

        return 0;
    }


    /**
     * Updates JSON:API configuration
     *
     * @return int 0 on success, 1 on failure
     */
    protected function jsonapi() : int
    {
        $done = 0;
        $filename = 'config/jsonapi.php';
        $content = file_get_contents( base_path( $filename ) );

        $string = "
        'cms' => \Aimeos\Cms\JsonApi\V1\Server::class,
        ";

        if( strpos( $content, '\Aimeos\Cms\JsonApi\V1\Server::class' ) === false && ++$done )
        {
            $content = str_replace( "'servers' => [", "'servers' => [" . $string, $content );
            $this->line( sprintf( '  Added CMS JSON:API server to [%1$s]' . PHP_EOL, $filename ) );
        }

        if( $done ) {
            file_put_contents( base_path( $filename ), $content );
        } else {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }

        return 0;
    }


    /**
     * Updates Lighthouse configuration
     *
     * @return int 0 on success, 1 on failure
     */
    protected function lighthouse() : int
    {
        $done = 0;
        $filename = 'config/lighthouse.php';
        $content = file_get_contents( base_path( $filename ) );

        $string = ", 'Aimeos\\\\Cms\\\\Models'";

        if( strpos( $content, $string ) === false && ++$done )
        {
            $content = str_replace( "'App\\\\Models'", "'App\\\\Models'" . $string, $content );
            $this->line( sprintf( '  Added CMS models directory to [%1$s]' . PHP_EOL, $filename ) );
        }

        $string = ", 'Aimeos\\\\Cms\\\\GraphQL\\\\Mutations'";

        if( strpos( $content, $string ) === false && ++$done )
        {
            $content = str_replace( " 'App\\\\GraphQL\\\\Mutations'", " ['App\\\\GraphQL\\\\Mutations'" . $string . "]", $content );
            $this->line( sprintf( '  Added CMS mutations directory to [%1$s]' . PHP_EOL, $filename ) );
        }

        if( strpos( $content, $string ) === false && ++$done )
        {
            $content = str_replace( "['App\\\\GraphQL\\\\Mutations'", "['App\\\\GraphQL\\\\Mutations'" . $string, $content );
            $this->line( sprintf( '  Added CMS mutations directory to [%1$s]' . PHP_EOL, $filename ) );
        }

        $string = "
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ";

        if( strpos( $content, '\Illuminate\Session\Middleware\StartSession::class' ) === false && ++$done )
        {
            $content = str_replace( "'middleware' => [", "'middleware' => [" . $string, $content );
            $this->line( sprintf( '  Added EncryptCookies/AddQueuedCookiesToResponse/StartSession middlewares to [%1$s]' . PHP_EOL, $filename ) );
        }

        if( $done ) {
            file_put_contents( base_path( $filename ), $content );
        } else {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }

        return 0;
    }


    /**
     * Updates routes file
     *
     * @return int 0 on success, 1 on failure
     */
    protected function route() : int
    {
        $filename = 'routes/web.php';
        $content = file_get_contents( base_path( $filename ) );

        $string = "Route::get('{slug?}/{lang?}', [\Aimeos\Cms\Http\Controllers\PageController::class, 'index'])->name('cms.page');";

        if( strpos( $content, '{slug' ) === false )
        {
            file_put_contents( base_path( $filename ), $content . "\n\n" . $string );
            $this->line( sprintf( '  File [%1$s] updated' . PHP_EOL, $filename ) );
        }
        else
        {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }


        $filename = 'routes/api.php';
        $content = file_get_contents( base_path( $filename ) );

        $string = '
\LaravelJsonApi\Laravel\Facades\JsonApiRoute::server("cms")->prefix("cms")->resources(function($server) {
    $server->resource("pages", \LaravelJsonApi\Laravel\Http\Controllers\JsonApiController::class)->readOnly()
        ->relationships(function ($relationships) {
            $relationships->hasOne("content")->readOnly();
        });
});';

        if( strpos( $content, '->resource("pages"' ) === false )
        {
            file_put_contents( base_path( $filename ), $content . PHP_EOL . $string );
            $this->line( sprintf( '  File [%1$s] updated' . PHP_EOL, $filename ) );
        }
        else
        {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }

        return 0;
    }


    /**
     * Updates Lighthouse GraphQL schema file
     *
     * @return int 0 on success, 1 on failure
     */
    protected function schema() : int
    {
        $filename = 'graphql/schema.graphql';
        $content = file_get_contents( base_path( $filename ) );

        $string = '#import cms.graphql';

        if( strpos( $content, $string ) === false )
        {
            file_put_contents( base_path( $filename ), $content . "\n\n" . $string );
            $this->line( sprintf( '  File [%1$s] updated' . PHP_EOL, $filename ) );
        }
        else
        {
            $this->line( sprintf( '  File [%1$s] already up to date' . PHP_EOL, $filename ) );
        }

        return 0;
    }
}
