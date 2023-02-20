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

		$this->publishes( [$basedir . '/config/cms.php' => config_path( 'cms.php' )], 'cms' );
		$this->publishes( [$basedir . '/graphql/cms.graphql' => base_path( 'graphql/cms.graphql' )], 'cms' );


		if( $this->app->runningInConsole() )
		{
			$this->commands( [
				\Aimeos\Cms\Commands\Install::class,
				\Aimeos\Cms\Commands\User::class,
			] );
		}
	}


	/**
	 * Register the service provider.
	 */
	public function register()
	{
	}
}