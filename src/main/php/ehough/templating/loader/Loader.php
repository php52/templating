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
 * Loader is the base class for all template loader classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ehough_templating_loader_Loader implements ehough_templating_loader_LoaderInterface
{
    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $logger;

    /**
     * @deprecated Deprecated in 2.4, to be removed in 3.0. Use $this->logger instead.
     */
    protected $debugger;

    /**
     * Sets the debug logger to use for this loader.
     *
     * @param \Psr\Log\LoggerInterface $logger A logger instance
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sets the debugger to use for this loader.
     *
     * @param ehough_templating_DebuggerInterface $debugger A debugger instance
     *
     * @deprecated Deprecated in 2.4, to be removed in 3.0. Use $this->setLogger() instead.
     */
    public function setDebugger(ehough_templating_DebuggerInterface $debugger)
    {
        $this->debugger = $debugger;
    }
}
