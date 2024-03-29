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
 * ehough_templating_EngineInterface is the interface each engine must implement.
 *
 * All methods relies on a template name. A template name is a
 * "logical" name for the template, and as such it does not refer to
 * a path on the filesystem (in fact, the template can be stored
 * anywhere, like in a database).
 *
 * The methods should accept any name. If the name is not an instance of
 * ehough_templating_TemplateReferenceInterface, a ehough_templating_TemplateNameParserInterface should be used to
 * convert the name to a ehough_templating_TemplateReferenceInterface instance.
 *
 * Each template loader uses the logical template name to look for
 * the template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface ehough_templating_EngineInterface
{
    /**
     * Renders a template.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name       A template name or a ehough_templating_TemplateReferenceInterface instance
     * @param array                                               $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws RuntimeException if the template cannot be rendered
     *
     * @api
     */
    function render($name, array $parameters = array());

    /**
     * Returns true if the template exists.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name A template name or a ehough_templating_TemplateReferenceInterface instance
     *
     * @return bool    true if the template exists, false otherwise
     *
     * @throws RuntimeException if the engine cannot handle the template name
     *
     * @api
     */
    function exists($name);

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string|ehough_templating_TemplateReferenceInterface $name A template name or a ehough_templating_TemplateReferenceInterface instance
     *
     * @return bool    true if this class supports the given template, false otherwise
     *
     * @api
     */
    function supports($name);
}
