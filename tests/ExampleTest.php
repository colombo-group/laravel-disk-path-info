<?php

namespace Colombo\LaravelDiskPathInfo\Tests;

use Orchestra\Testbench\TestCase;
use Colombo\LaravelDiskPathInfo\LaravelDiskPathInfoServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelDiskPathInfoServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
