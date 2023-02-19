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
		$this->loadViewsFrom( dirname( __DIR__ ) . '/views', 'cms' );
		$this->loadMigrationsFrom( dirname( __DIR__ ) . '/database/migrations' );
		$this->publishes( [dirname( __DIR__ ) . '/config/cms.php' => config_path( 'cms.php' )], 'config' );
	}


	/**
	 * Register the service provider.
	 */
	public function register()
	{
	}
}