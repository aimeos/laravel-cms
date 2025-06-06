<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;


class CmsServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 */
	protected bool $defer = false;


	/**
	 * Bootstrap the application events.
	 */
	public function boot()
	{
		$basedir = dirname( __DIR__ );

		$this->loadViewsFrom( $basedir . '/views', 'cms' );
		$this->loadMigrationsFrom( $basedir . '/database/migrations' );

		$this->publishes( [$basedir . '/public' => public_path( 'vendor/cms' )], 'public' );
		$this->publishes( [$basedir . '/config/cms.php' => config_path( 'cms.php' )], 'config' );
		$this->publishes( [$basedir . '/admin/dist' => public_path( 'vendor/cms/admin' )], 'admin' );
		$this->publishes( [$basedir . '/graphql' => base_path( 'graphql' )], 'admin' );


		if( $this->app->runningInConsole() )
		{
			$this->commands( [
				\Aimeos\Cms\Commands\Install::class,
				\Aimeos\Cms\Commands\Publish::class,
				\Aimeos\Cms\Commands\Serve::class,
				\Aimeos\Cms\Commands\User::class,
			] );
		}

        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen( function( $request ) {
            return $request->is( trim( config( 'lighthouse.route.uri' ), '/' ) );
        } );
	}


	/**
	 * Register the service provider.
	 */
	public function register()
	{
	}
}