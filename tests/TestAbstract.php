<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;


abstract class TestAbstract extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use InteractsWithViews;
    use WithLaravelMigrations;


    protected ?\App\Models\User $user = null;
    protected $enablesPackageDiscoveries = true;


    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations(['--database' => 'testing']);
    }


	protected function defineEnvironment( $app )
	{
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'test_',
        ]);

        $app['config']->set( 'cms.db', 'testing' );

        \Aimeos\Cms\Tenancy::$callback = function() {
            return 'test';
        };
    }


	protected function getPackageProviders( $app )
	{
		return [
			'Aimeos\Cms\CmsServiceProvider',
			'Kalnoy\Nestedset\NestedSetServiceProvider',
		];
	}
}