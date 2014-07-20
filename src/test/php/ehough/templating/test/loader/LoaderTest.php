<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!interface_exists('ehough_templating_test_MockLoggerInterface', false)) {

    require dirname(__FILE__) . '/../MockLoggerInterface.php';
}
class ehough_templating_test_loader_LoaderTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetLogger()
    {
        $loader = new ProjectTemplateLoader4();
        $logger = $this->getMock('ehough_templating_test_MockLoggerInterface');
        $loader->setLogger($logger);
        $this->assertSame($logger, $loader->getLogger(), '->setLogger() sets the logger instance');
    }

    public function testGetSetDebugger()
    {
        $loader = new ProjectTemplateLoader4();
        $debugger = $this->getMock('ehough_templating_DebuggerInterface');
        $loader->setDebugger($debugger);
        $this->assertSame($debugger, $loader->getDebugger(), '->setDebugger() sets the debugger instance');
    }
}

class ProjectTemplateLoader4 extends ehough_templating_loader_Loader
{
    public function load(ehough_templating_TemplateReferenceInterface $template)
    {
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getDebugger()
    {
        return $this->debugger;
    }

    public function isFresh(ehough_templating_TemplateReferenceInterface $template, $time)
    {
        return false;
    }
}
