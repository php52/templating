<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_templating_DelegatingEngineTest extends PHPUnit_Framework_TestCase
{
    public function testRenderDelegatesToSupportedEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', true);

        $secondEngine->expects($this->once())
            ->method('render')
            ->with('template.php', array('foo' => 'bar'))
            ->will($this->returnValue('<html />'));

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($firstEngine, $secondEngine));
        $result = $delegatingEngine->render('template.php', array('foo' => 'bar'));

        $this->assertSame('<html />', $result);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No engine is able to work with the template "template.php"
     */
    public function testRenderWithNoSupportedEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($firstEngine, $secondEngine));
        $delegatingEngine->render('template.php', array('foo' => 'bar'));
    }

    public function testStreamDelegatesToSupportedEngine()
    {
        $streamingEngine = $this->getStreamingEngineMock('template.php', true);
        $streamingEngine->expects($this->once())
            ->method('stream')
            ->with('template.php', array('foo' => 'bar'))
            ->will($this->returnValue('<html />'));

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($streamingEngine));
        $result = $delegatingEngine->stream('template.php', array('foo' => 'bar'));

        $this->assertNull($result);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Template "template.php" cannot be streamed as the engine supporting it does not implement ehough_templating_StreamingEngineInterface
     */
    public function testStreamRequiresStreamingEngine()
    {
        $engine = $this->getEngineMock('template.php', true);
        $engine->expects($this->never())->method('stream');

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($engine));
        $delegatingEngine->stream('template.php', array('foo' => 'bar'));
    }

    public function testExists()
    {
        $engine = $this->getEngineMock('template.php', true);
        $engine->expects($this->once())
            ->method('exists')
            ->with('template.php')
            ->will($this->returnValue(true));

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($engine));

        $this->assertTrue($delegatingEngine->exists('template.php'));
    }

    public function testSupports()
    {
        $engine = $this->getEngineMock('template.php', true);

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($engine));

        $this->assertTrue($delegatingEngine->supports('template.php'));
    }

    public function testSupportsWithNoSupportedEngine()
    {
        $engine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($engine));

        $this->assertFalse($delegatingEngine->supports('template.php'));
    }

    public function testGetExistingEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', true);

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($firstEngine, $secondEngine));

        $this->assertSame($secondEngine, $delegatingEngine->getEngine('template.php'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No engine is able to work with the template "template.php"
     */
    public function testGetInvalidEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new ehough_templating_DelegatingEngine(array($firstEngine, $secondEngine));
        $delegatingEngine->getEngine('template.php', array('foo' => 'bar'));
    }

    private function getEngineMock($template, $supports)
    {
        $engine = $this->getMock('ehough_templating_EngineInterface');

        $engine->expects($this->once())
            ->method('supports')
            ->with($template)
            ->will($this->returnValue($supports));

        return $engine;
    }

    private function getStreamingEngineMock($template, $supports)
    {
        $engine = $this->getMockForAbstractClass('ehough_templating_test_MyStreamingEngine');

        $engine->expects($this->once())
            ->method('supports')
            ->with($template)
            ->will($this->returnValue($supports));

        return $engine;
    }
}

interface ehough_templating_test_MyStreamingEngine extends ehough_templating_StreamingEngineInterface, ehough_templating_EngineInterface
{
}
