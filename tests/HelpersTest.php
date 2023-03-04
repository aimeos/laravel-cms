<?php

namespace Tests;


class HelpersTest extends \Orchestra\Testbench\TestCase
{
	public function testCmsImage()
	{
		$this->assertEquals( '/storage/not/exists.jpg 1w', cmsimage( [1 => 'not/exists.jpg'] ) );
	}


	public function testCmsUrl()
	{
		$this->assertEquals( 'data:ABCD', cmsurl( 'data:ABCD' ) );
		$this->assertEquals( '/storage/not/exists.jpg', cmsurl( 'not/exists.jpg' ) );
		$this->assertEquals( 'http://example.com/not/exists.jpg', cmsurl( 'http://example.com/not/exists.jpg' ) );
		$this->assertEquals( 'https://example.com/not/exists.jpg', cmsurl( 'https://example.com/not/exists.jpg' ) );
	}
}
