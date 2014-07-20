<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_test_storage_StorageTest extends PHPUnit_Framework_TestCase
{
    public function testMagicToString()
    {
        $storage = new TestStorage('foo');
        $this->assertEquals('foo', (string) $storage, '__toString() returns the template name');
    }
}

class TestStorage extends ehough_templating_storage_Storage
{
    public function getContent()
    {
    }
}
