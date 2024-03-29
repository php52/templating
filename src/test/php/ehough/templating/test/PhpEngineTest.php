<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class_exists('ehough_templating_test_helper_SimpleHelper') ||
    require dirname(__FILE__) . '/helper/SimpleHelper.php';

class ehough_templating_test_ehough_templating_PhpEngineTest extends PHPUnit_Framework_TestCase
{
    protected $loader;

    protected function setUp()
    {
        $this->loader = new ehough_templating_test_ProjectTemplateLoader();
    }

    protected function tearDown()
    {
        $this->loader = null;
    }

    public function testConstructor()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $this->assertEquals($this->loader, $engine->getLoader(), '__construct() takes a loader instance as its second first argument');
    }

    public function testOffsetGet()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $engine->set($helper = new ehough_templating_test_helper_SimpleHelper('bar'), 'foo');
        $this->assertEquals($helper, $engine['foo'], '->offsetGet() returns the value of a helper');

        try {
            $engine['bar'];
            $this->fail('->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "bar" is not defined.', $e->getMessage(), '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        }
    }

    public function testGetSetHas()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $foo = new ehough_templating_test_helper_SimpleHelper('foo');
        $engine->set($foo);
        $this->assertEquals($foo, $engine->get('foo'), '->set() sets a helper');

        $engine[$foo] = 'bar';
        $this->assertEquals($foo, $engine->get('bar'), '->set() takes an alias as a second argument');

        $this->assertTrue(isset($engine['bar']));

        try {
            $engine->get('foobar');
            $this->fail('->get() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->get() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "foobar" is not defined.', $e->getMessage(), '->get() throws an InvalidArgumentException if the helper is not defined');
        }

        $this->assertTrue(isset($engine['bar']));
        $this->assertTrue($engine->has('foo'), '->has() returns true if the helper exists');
        $this->assertFalse($engine->has('foobar'), '->has() returns false if the helper does not exist');
    }

    public function testUnsetHelper()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $foo = new ehough_templating_test_helper_SimpleHelper('foo');
        $engine->set($foo);

        $this->setExpectedException('LogicException');

        unset($engine['foo']);
    }

    public function testExtendRender()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader, array(), array(new ehough_templating_helper_SlotsHelper()));
        try {
            $engine->render('name');
            $this->fail('->render() throws an InvalidArgumentException if the template does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->render() throws an InvalidArgumentException if the template does not exist');
            $this->assertEquals('The template "name" does not exist.', $e->getMessage(), '->render() throws an InvalidArgumentException if the template does not exist');
        }

        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader, array(new ehough_templating_helper_SlotsHelper()));
        $engine->set(new ehough_templating_test_helper_SimpleHelper('bar'));
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $view[\'foo\'].$foo ?>');
        $this->loader->setTemplate('layout.php', '-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('-barfoo-', $engine->render('foo.php', array('foo' => 'foo')), '->render() uses the decorator to decorate the template');

        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader, array(new ehough_templating_helper_SlotsHelper()));
        $engine->set(new ehough_templating_test_helper_SimpleHelper('bar'));
        $this->loader->setTemplate('bar.php', 'bar');
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $foo ?>');
        $this->loader->setTemplate('layout.php', '<?php echo $view->render("bar.php") ?>-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('bar-foo-', $engine->render('foo.php', array('foo' => 'foo', 'bar' => 'bar')), '->render() supports render() calls in templates');
    }

    public function testRenderParameter()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $this->loader->setTemplate('foo.php', '<?php echo $template . $parameters ?>');
        $this->assertEquals('foobar', $engine->render('foo.php', array('template' => 'foo', 'parameters' => 'bar')), '->render() extract variables');
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider forbiddenParameterNames
     */
    public function testRenderForbiddenParameter($name)
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $this->loader->setTemplate('foo.php', 'bar');
        $engine->render('foo.php', array($name => 'foo'));
    }

    public function forbiddenParameterNames()
    {
        return array(
            array('this'),
            array('view'),
        );
    }

    public function testEscape()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $this->assertEquals('&lt;br /&gt;', $engine->escape('<br />'), '->escape() escapes strings');
        $foo = new stdClass();
        $this->assertEquals($foo, $engine->escape($foo), '->escape() does nothing on non strings');
    }

    public function testGetSetCharset()
    {
        $helper = new ehough_templating_helper_SlotsHelper();
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader, array($helper));
        $this->assertEquals('UTF-8', $engine->getCharset(), 'ehough_templating_EngineInterface::getCharset() returns UTF-8 by default');
        $this->assertEquals('UTF-8', $helper->getCharset(), 'ehough_templating_helper_HelperInterface::getCharset() returns UTF-8 by default');

        $engine->setCharset('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $engine->getCharset(), 'ehough_templating_EngineInterface::setCharset() changes the default charset to use');
        $this->assertEquals('ISO-8859-1', $helper->getCharset(), 'ehough_templating_EngineInterface::setCharset() changes the default charset of helper');
    }

    public function testGlobalVariables()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $engine->addGlobal('global_variable', 'lorem ipsum');

        $this->assertEquals(array(
            'global_variable' => 'lorem ipsum',
        ), $engine->getGlobals());
    }

    public function testGlobalsGetPassedToTemplate()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);
        $engine->addGlobal('global', 'global variable');

        $this->loader->setTemplate('global.php', '<?php echo $global; ?>');

        $this->assertEquals($engine->render('global.php'), 'global variable');

        $this->assertEquals($engine->render('global.php', array('global' => 'overwritten')), 'overwritten');
    }

    public function testGetLoader()
    {
        $engine = new ProjectTemplateEngine(new ehough_templating_TemplateNameParser(), $this->loader);

        $this->assertSame($this->loader, $engine->getLoader());
    }
}

class ProjectTemplateEngine extends ehough_templating_PhpEngine
{
    public function getLoader()
    {
        return $this->loader;
    }
}

class ehough_templating_test_ProjectTemplateLoader extends ehough_templating_loader_Loader
{
    public $templates = array();

    public function setTemplate($name, $content)
    {
        $template = new ehough_templating_TemplateReference($name, 'php');
        $this->templates[$template->getLogicalName()] = $content;
    }

    public function load(ehough_templating_TemplateReferenceInterface $template)
    {
        if (isset($this->templates[$template->getLogicalName()])) {
            return new ehough_templating_storage_StringStorage($this->templates[$template->getLogicalName()]);
        }

        return false;
    }

    public function isFresh(ehough_templating_TemplateReferenceInterface $template, $time)
    {
        return false;
    }
}
