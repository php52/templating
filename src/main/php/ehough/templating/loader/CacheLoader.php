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
 * ehough_templating_loader_CacheLoader is a loader that caches other loaders responses
 * on the filesystem.
 *
 * This cache only caches on disk to allow PHP accelerators to cache the opcodes.
 * All other mechanism would imply the use of `eval()`.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_templating_loader_CacheLoader extends ehough_templating_loader_Loader
{
    protected $loader;
    protected $dir;

    /**
     * Constructor.
     *
     * @param ehough_templating_loader_LoaderInterface $loader A Loader instance
     * @param string          $dir    The directory where to store the cache files
     */
    public function __construct(ehough_templating_loader_LoaderInterface $loader, $dir)
    {
        $this->loader = $loader;
        $this->dir = $dir;
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
        $key = hash('sha256', $template->getLogicalName());
        $dir = $this->dir.DIRECTORY_SEPARATOR.substr($key, 0, 2);
        $file = substr($key, 2).'.tpl';
        $path = $dir.DIRECTORY_SEPARATOR.$file;

        if (is_file($path)) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Fetching template "%s" from cache', $template->get('name')));
            } elseif (null !== $this->debugger) {
                // just for BC, to be removed in 3.0
                $this->debugger->log(sprintf('Fetching template "%s" from cache', $template->get('name')));
            }

            return new ehough_templating_storage_FileStorage($path);
        }

        if (false === $storage = $this->loader->load($template)) {
            return false;
        }

        $content = $storage->getContent();

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $content);

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Storing template "%s" in cache', $template->get('name')));
        } elseif (null !== $this->debugger) {
            // just for BC, to be removed in 3.0
            $this->debugger->log(sprintf('Storing template "%s" in cache', $template->get('name')));
        }

        return new ehough_templating_storage_FileStorage($path);
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
        return $this->loader->isFresh($template, $time);
    }
}
