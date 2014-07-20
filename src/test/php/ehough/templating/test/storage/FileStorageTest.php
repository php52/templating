<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_test_storage_FileStorageTest extends PHPUnit_Framework_TestCase
{
    public function testGetContent()
    {
        $storage = new ehough_templating_storage_FileStorage('foo');
        $this->assertInstanceOf('ehough_templating_storage_Storage', $storage, 'ehough_templating_storage_FileStorage is an instance of Storage');
        $storage = new ehough_templating_storage_FileStorage(__DIR__.'/../../../../../resources/Fixtures/templates/foo.php');
        $this->assertEquals('<?php echo $foo ?>'."\n", $storage->getContent(), '->getContent() returns the content of the template');
    }
}
