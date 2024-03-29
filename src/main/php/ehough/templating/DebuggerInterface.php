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
 * ehough_templating_DebuggerInterface is the interface you need to implement
 * to debug template loader instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated Deprecated in 2.4, to be removed in 3.0. Use Psr\Log\LoggerInterface instead.
 */
interface ehough_templating_DebuggerInterface
{
    /**
     * Logs a message.
     *
     * @param string $message A message to log
     */
    function log($message);
}
