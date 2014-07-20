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
 * ehough_templating_TemplateNameParserInterface converts template names to ehough_templating_TemplateReferenceInterface
 * instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface ehough_templating_TemplateNameParserInterface
{
    /**
     * Convert a template name to a ehough_templating_TemplateReferenceInterface instance.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name A template name or a ehough_templating_TemplateReferenceInterface instance
     *
     * @return ehough_templating_TemplateReferenceInterface A template
     *
     * @api
     */
    function parse($name);
}
