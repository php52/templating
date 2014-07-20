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
 * Asset package interface.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface ehough_templating_asset_PackageInterface
{
    /**
     * Returns the asset package version.
     *
     * @return string The version string
     */
    function getVersion();

    /**
     * Returns an absolute or root-relative public path.
     *
     * @param string           $path    A path
     * @param string|bool|null $version A specific version for the path
     *
     * @return string The public path
     */
    function getUrl($path, $version = null);
}
