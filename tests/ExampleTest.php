<?php

namespace Colombo\Libs\DiskPathTools\Tests;

use Colombo\Libs\DiskPathTools\DiskPathInfo;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
    
    public function test_create_disk_path_info() {
        $disk = 'local';
        $path = 'test.txt';
        $path_info = new DiskPathInfo($disk, $path);
        dump("Disk path info: ",$path_info, "$path_info");
        $this->assertTrue(true);
    }
}
