<?php

namespace Colombo\LaravelDiskPathInfo;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Colombo\LaravelDiskPathInfo\Skeleton\SkeletonClass
 */
class LaravelDiskPathInfoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-disk-path-info';
    }
}
