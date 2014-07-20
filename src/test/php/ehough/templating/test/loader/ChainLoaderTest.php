<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_test_loader_ChainLoaderTest extends PHPUnit_Framework_TestCase
{
    protected $loader1;
    protected $loader2;

    protected function setUp()
    {
        $fixturesPath = realpath(__DIR__.'/../../../../../resources/Fixtures/');
        $this->loader1 = new ehough_templating_loader_FilesystemLoader($fixturesPath.'/null/%name%');
        $this->loader2 = new ehough_templating_loader_FilesystemLoader($fixturesPath.'/templates/%name%');
    }

    public function testConstructor()
    {
        $loader = new ProjectTemplateLoader1(array($this->loader1, $this->loader2));
        $this->assertEquals(array($this->loader1, $this->loader2), $loader->getLoaders(), '__construct() takes an array of template loaders as its second argument');
    }

    public function testAddLoader()
    {
        $loader = new ProjectTemplateLoader1(array($this->loader1));
        $loader->addLoader($this->loader2);
        $this->assertEquals(array($this->loader1, $this->loader2), $loader->getLoaders(), '->addLoader() adds a template loader at the end of the loaders');
    }

    public function testLoad()
    {
        $loader = new ProjectTemplateLoader1(array($this->loader1, $this->loader2));
        $this->assertFalse($loader->load(new ehough_templating_TemplateReference('bar', 'php')), '->load() returns false if the template is not found');
        $this->assertFalse($loader->load(new ehough_templating_TemplateReference('foo', 'php')), '->load() returns false if the template does not exist for the given renderer');
        $this->assertInstanceOf(
            'ehough_templating_storage_FileStorage',
            $loader->load(new ehough_templating_TemplateReference('foo.php', 'php')),
            '->load() returns a ehough_templating_storage_FileStorage if the template exists'
        );
    }
}

class ProjectTemplateLoader1 extends ehough_templating_loader_ChainLoader
{
    public function getLoaders()
    {
        return $this->loaders;
    }
}
