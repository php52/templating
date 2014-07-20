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
 * ehough_templating_loader_ChainLoader is a loader that calls other loaders to load templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_templating_loader_ChainLoader extends ehough_templating_loader_Loader
{
    /**
     * @var ehough_templating_loader_LoaderInterface[]
     */
    protected $loaders = array();

    /**
     * Constructor.
     *
     * @param ehough_templating_loader_LoaderInterface[] $loaders An array of loader instances
     */
    public function __construct(array $loaders = array())
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Adds a loader instance.
     *
     * @param ehough_templating_loader_LoaderInterface $loader A Loader instance
     */
    public function addLoader(ehough_templating_loader_LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Loads a template.
     *
     * @param ehough_templating_TemplateReferenceInterface $template A template
     *
     * @return ehough_templating_storage_Storage|bool    false if the template cannot be loaded, a ehough_templating_storage_Storage instance otherwise
     */
    public function load(ehough_templating_TemplateReferenceInterface $template)
    {
        foreach ($this->loaders as $loader) {
            if (false !== $storage = $loader->load($template)) {
                return $storage;
            }
        }

        return false;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param ehough_templating_TemplateReferenceInterface $template A template
     * @param int                        $time     The last modification time of the cached template (timestamp)
     *
     * @return bool
     */
    public function isFresh(ehough_templating_TemplateReferenceInterface $template, $time)
    {
        foreach ($this->loaders as $loader) {
            return $loader->isFresh($template, $time);
        }

        return false;
    }
}
