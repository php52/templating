<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ehough_templating_DelegatingEngine selects an engine for a given template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ehough_templating_DelegatingEngine implements ehough_templating_EngineInterface, ehough_templating_StreamingEngineInterface
{
    /**
     * @var ehough_templating_EngineInterface[]
     */
    protected $engines = array();

    /**
     * Constructor.
     *
     * @param ehough_templating_EngineInterface[] $engines An array of ehough_templating_EngineInterface instances to add
     *
     * @api
     */
    public function __construct(array $engines = array())
    {
        foreach ($engines as $engine) {
            $this->addEngine($engine);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function render($name, array $parameters = array())
    {
        return $this->getEngine($name)->render($name, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function stream($name, array $parameters = array())
    {
        $engine = $this->getEngine($name);
        if (!$engine instanceof ehough_templating_StreamingEngineInterface) {
            throw new LogicException(sprintf('Template "%s" cannot be streamed as the engine supporting it does not implement ehough_templating_StreamingEngineInterface.', $name));
        }

        $engine->stream($name, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function exists($name)
    {
        return $this->getEngine($name)->exists($name);
    }

    /**
     * Adds an engine.
     *
     * @param ehough_templating_EngineInterface $engine An ehough_templating_EngineInterface instance
     *
     * @api
     */
    public function addEngine(ehough_templating_EngineInterface $engine)
    {
        $this->engines[] = $engine;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function supports($name)
    {
        try {
            $this->getEngine($name);
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get an engine able to render the given template.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name A template name or a ehough_templating_TemplateReferenceInterface instance
     *
     * @return ehough_templating_EngineInterface The engine
     *
     * @throws RuntimeException if no engine able to work with the template is found
     *
     * @api
     */
    public function getEngine($name)
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($name)) {
                return $engine;
            }
        }

        throw new RuntimeException(sprintf('No engine is able to work with the template "%s".', $name));
    }
}
