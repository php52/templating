<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_test_asset_CoreAssetsHelperTest extends PHPUnit_Framework_TestCase
{
    protected $package;

    protected function setUp()
    {
        $this->package = $this->getMock('ehough_templating_asset_PackageInterface');
    }

    protected function tearDown()
    {
        $this->package = null;
    }

    public function testAddGetPackage()
    {
        $helper = new ehough_templating_helper_CoreAssetsHelper($this->package);

        $helper->addPackage('foo', $this->package);

        $this->assertSame($this->package, $helper->getPackage('foo'));
    }

    public function testGetNonexistingPackage()
    {
        $helper = new ehough_templating_helper_CoreAssetsHelper($this->package);

        $this->setExpectedException('InvalidArgumentException');

        $helper->getPackage('foo');
    }

    public function testGetHelperName()
    {
        $helper = new ehough_templating_helper_CoreAssetsHelper($this->package);

        $this->assertEquals('assets', $helper->getName());
    }
}
