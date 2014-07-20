<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_test_loader_FilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(__DIR__.'/../Fixtures/');
    }

    public function testConstructor()
    {
        $pathPattern = self::$fixturesPath.'/templates/%name%.%engine%';
        $path = self::$fixturesPath.'/templates';
        $loader = new ProjectTemplateLoader2($pathPattern);
        $this->assertEquals(array($pathPattern), $loader->getTemplatePathPatterns(), '__construct() takes a path as its second argument');
        $loader = new ProjectTemplateLoader2(array($pathPattern));
        $this->assertEquals(array($pathPattern), $loader->getTemplatePathPatterns(), '__construct() takes an array of paths as its second argument');
    }

    public function testIsAbsolutePath()
    {
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('c:\\\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('c:/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('\\server\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('https://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(ProjectTemplateLoader2::isAbsolutePath('phar://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
    }

    public function testLoad()
    {
        $pathPattern = self::$fixturesPath.'/templates/%name%';
        $path = self::$fixturesPath.'/templates';
        $loader = new ProjectTemplateLoader2($pathPattern);
        $storage = $loader->load(new ehough_templating_TemplateReference($path.'/foo.php', 'php'));
        $this->assertInstanceOf('ehough_templating_storage_FileStorage', $storage, '->load() returns a ehough_templating_storage_FileStorage if you pass an absolute path');
        $this->assertEquals($path.'/foo.php', (string) $storage, '->load() returns a ehough_templating_storage_FileStorage pointing to the passed absolute path');

        $this->assertFalse($loader->load(new ehough_templating_TemplateReference('bar', 'php')), '->load() returns false if the template is not found');

        $storage = $loader->load(new ehough_templating_TemplateReference('foo.php', 'php'));
        $this->assertInstanceOf('ehough_templating_storage_FileStorage', $storage, '->load() returns a ehough_templating_storage_FileStorage if you pass a relative template that exists');
        $this->assertEquals($path.'/foo.php', (string) $storage, '->load() returns a ehough_templating_storage_FileStorage pointing to the absolute path of the template');

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->exactly(2))->method('debug');

        $loader = new ProjectTemplateLoader2($pathPattern);
        $loader->setLogger($logger);
        $this->assertFalse($loader->load(new ehough_templating_TemplateReference('foo.xml', 'php')), '->load() returns false if the template does not exist for the given engine');

        $loader = new ProjectTemplateLoader2(array(self::$fixturesPath.'/null/%name%', $pathPattern));
        $loader->setLogger($logger);
        $loader->load(new ehough_templating_TemplateReference('foo.php', 'php'));
    }
}

class ProjectTemplateLoader2 extends ehough_templating_loader_FilesystemLoader
{
    public function getTemplatePathPatterns()
    {
        return $this->templatePathPatterns;
    }

    public static function isAbsolutePath($path)
    {
        return parent::isAbsolutePath($path);
    }
}
