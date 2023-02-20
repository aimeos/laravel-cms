<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Commands;

use Illuminate\Console\Command;


class Install extends Command
{
    /**
     * Command name
     */
    protected $signature = 'cms:install';

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

        $this->comment( '  Migrating database ...' );
        $result += $this->call( 'migrate' );

        $this->comment( '  Publishing Lighthouse schema ...' );
        $result += $this->call( 'vendor:publish', ['--tag' => 'lighthouse-schema'] );

        $this->comment( '  Publishing Lighthouse configuration ...' );
        $result += $this->call( 'vendor:publish', ['--tag' => 'lighthouse-config'] );

        $this->comment( '  Updating Lighthouse configuration ...' );
        $result += $this->lighthouse();

        $this->comment( '  Adding Laravel CMS GraphQL schema ...' );
        $result += $this->schema();

        $this->comment( '  Adding Laravel CMS route ...' );
        $result += $this->route();

        if( $result ) {
            $this->error( '  Error during Laravel CMS installation!' );
        } else {
            $this->info( '  Laravel CMS has been installed successfully' );
        }
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

        $string = "Route::get('{slug}', [\Aimeos\Cms\Http\Controllers\PageController::class, 'index'])->name('cms.page');";

        if( strpos( $content, '{slug}' ) === false )
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
