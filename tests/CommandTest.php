<?php

namespace Tests;

use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Element;
use Database\Seeders\CmsSeeder;


class CommandTest extends TestAbstract
{
    public function testPublish(): void
    {
        $this->seed( CmsSeeder::class );

        $this->artisan('cms:publish')->assertExitCode (0 );

        $this->assertEquals( 1, Page::where( 'slug', 'hidden' )->firstOrFail()?->status );
        $this->assertEquals( 'Powered by Laravel CMS!', Element::where( 'name', 'Shared footer' )->firstOrFail()?->data->data->text );
    }
}