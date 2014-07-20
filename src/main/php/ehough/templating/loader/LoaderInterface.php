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
 * ehough_templating_loader_LoaderInterface is the interface all loaders must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface ehough_templating_loader_LoaderInterface
{
    /**
     * Loads a template.
     *
     * @param ehough_templating_TemplateReferenceInterface $template A template
     *
     * @return ehough_templating_storage_Storage|bool    false if the template cannot be loaded, a ehough_templating_storage_Storage instance otherwise
     *
     * @api
     */
    function load(ehough_templating_TemplateReferenceInterface $template);

    /**
     * Returns true if the template is still fresh.
     *
     * @param ehough_templating_TemplateReferenceInterface $template A template
     * @param int                                          $time     The last modification time of the cached template (timestamp)
     *
     * @return bool
     *
     * @api
     */
    function isFresh(ehough_templating_TemplateReferenceInterface $template, $time);
}
