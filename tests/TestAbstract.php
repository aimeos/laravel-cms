<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;


abstract class TestAbstract extends \Orchestra\Testbench\TestCase
{
	use InteractsWithViews;


	protected function defineEnvironment( $app )
	{
		$app['config']->set( 'cms.db', 'testing' );
		$app['config']->set( 'jsonapi.servers.cms', \Aimeos\Cms\JsonApi\V1\Server::class );
	}


	protected function getPackageProviders( $app )
	{
		return [
			'Aimeos\Cms\CmsServiceProvider',
			'Kalnoy\Nestedset\NestedSetServiceProvider',
			'LaravelJsonApi\Laravel\ServiceProvider'
		];
	}
}