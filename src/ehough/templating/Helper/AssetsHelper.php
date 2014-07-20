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
 * AssetsHelper helps manage asset URLs.
 *
 * Usage:
 *
 * <code>
 *   <img src="<?php echo $view['assets']->getUrl('foo.png') ?>" />
 * </code>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kris Wallsmith <kris@symfony.com>
 */
class ehough_templating_helper_AssetsHelper extends ehough_templating_helper_CoreAssetsHelper
{
    /**
     * Constructor.
     *
     * @param string       $basePath      The base path
     * @param string|array $baseUrls      Base asset URLs
     * @param string       $version       The asset version
     * @param string       $format        The version format
     * @param array        $namedPackages Additional packages
     */
    public function __construct($basePath = null, $baseUrls = array(), $version = null, $format = null, $namedPackages = array())
    {
        if ($baseUrls) {
            $defaultPackage = new ehough_templating_asset_UrlPackage($baseUrls, $version, $format);
        } else {
            $defaultPackage = new ehough_templating_asset_PathPackage($basePath, $version, $format);
        }

        parent::__construct($defaultPackage, $namedPackages);
    }
}
