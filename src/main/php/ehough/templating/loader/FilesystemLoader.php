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
 * ehough_templating_loader_FilesystemLoader is a loader that read templates from the filesystem.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ehough_templating_loader_FilesystemLoader extends ehough_templating_loader_Loader
{
    protected $templatePathPatterns;

    /**
     * Constructor.
     *
     * @param array $templatePathPatterns An array of path patterns to look for templates
     *
     * @api
     */
    public function __construct($templatePathPatterns)
    {
        $this->templatePathPatterns = (array) $templatePathPatterns;
    }

    /**
     * Loads a template.
     *
     * @param ehough_templating_TemplateReferenceInterface $template A template
     *
     * @return ehough_templating_storage_Storage|bool    false if the template cannot be loaded, a ehough_templating_storage_Storage instance otherwise
     *
     * @api
     */
    public function load(ehough_templating_TemplateReferenceInterface $template)
    {
        $file = $template->get('name');

        if (self::isAbsolutePath($file) && is_file($file)) {
            return new ehough_templating_storage_FileStorage($file);
        }

        $replacements = array();
        foreach ($template->all() as $key => $value) {
            $replacements['%'.$key.'%'] = $value;
        }

        $logs = array();
        foreach ($this->templatePathPatterns as $templatePathPattern) {
            if (is_file($file = strtr($templatePathPattern, $replacements)) && is_readable($file)) {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Loaded template file "%s"', $file));
                } elseif (null !== $this->debugger) {
                    // just for BC, to be removed in 3.0
                    $this->debugger->log(sprintf('Loaded template file "%s"', $file));
                }

                return new ehough_templating_storage_FileStorage($file);
            }

            if (null !== $this->logger || null !== $this->debugger) {
                $logs[] = sprintf('Failed loading template file "%s"', $file);
            }
        }

        foreach ($logs as $log) {
            if (null !== $this->logger) {
                $this->logger->debug($log);
            } elseif (null !== $this->debugger) {
                $this->debugger->log($log);
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
     * @return bool    true if the template is still fresh, false otherwise
     *
     * @api
     */
    public function isFresh(ehough_templating_TemplateReferenceInterface $template, $time)
    {
        if (false === $storage = $this->load($template)) {
            return false;
        }

        return filemtime((string) $storage) < $time;
    }

    /**
     * Returns true if the file is an existing absolute path.
     *
     * @param string $file A path
     *
     * @return bool    true if the path exists and is absolute, false otherwise
     */
    protected static function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
