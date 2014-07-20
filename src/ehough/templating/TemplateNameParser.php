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
 * ehough_templating_TemplateNameParser is the default implementation of ehough_templating_TemplateNameParserInterface.
 *
 * This implementation takes everything as the template name
 * and the extension for the engine.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ehough_templating_TemplateNameParser implements ehough_templating_TemplateNameParserInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function parse($name)
    {
        if ($name instanceof ehough_templating_TemplateReferenceInterface) {
            return $name;
        }

        $engine = null;
        if (false !== $pos = strrpos($name, '.')) {
            $engine = substr($name, $pos + 1);
        }

        return new ehough_templating_TemplateReference($name, $engine);
    }
}
