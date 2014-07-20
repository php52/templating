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
 * ehough_templating_helper_CoreAssetsHelper helps manage asset URLs.
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
class ehough_templating_helper_CoreAssetsHelper extends ehough_templating_helper_Helper implements ehough_templating_asset_PackageInterface
{
    protected $defaultPackage;
    protected $namedPackages = array();

    /**
     * Constructor.
     *
     * @param ehough_templating_asset_PackageInterface $defaultPackage The default package
     * @param array                                    $namedPackages  Additional packages indexed by name
     */
    public function __construct(ehough_templating_asset_PackageInterface $defaultPackage, array $namedPackages = array())
    {
        $this->defaultPackage = $defaultPackage;

        foreach ($namedPackages as $name => $package) {
            $this->addPackage($name, $package);
        }
    }

    /**
     * Sets the default package.
     *
     * @param ehough_templating_asset_PackageInterface $defaultPackage The default package
     */
    public function setDefaultPackage(ehough_templating_asset_PackageInterface $defaultPackage)
    {
        $this->defaultPackage = $defaultPackage;
    }

    /**
     * Adds an asset package to the helper.
     *
     * @param string                                   $name    The package name
     * @param ehough_templating_asset_PackageInterface $package The package
     */
    public function addPackage($name, ehough_templating_asset_PackageInterface $package)
    {
        $this->namedPackages[$name] = $package;
    }

    /**
     * Returns an asset package.
     *
     * @param string $name The name of the package or null for the default package
     *
     * @return ehough_templating_asset_PackageInterface An asset package
     *
     * @throws InvalidArgumentException If there is no package by that name
     */
    public function getPackage($name = null)
    {
        if (null === $name) {
            return $this->defaultPackage;
        }

        if (!isset($this->namedPackages[$name])) {
            throw new InvalidArgumentException(sprintf('There is no "%s" asset package.', $name));
        }

        return $this->namedPackages[$name];
    }

    /**
     * Gets the version to add to public URL.
     *
     * @param string $packageName A package name
     *
     * @return string The current version
     */
    public function getVersion($packageName = null)
    {
        return $this->getPackage($packageName)->getVersion();
    }

    /**
     * Returns the public path.
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string              $path        A public path
     * @param string              $packageName The name of the asset package to use
     * @param string|bool|null    $version     A specific version
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getUrl($path, $packageName = null, $version = null)
    {
        return $this->getPackage($packageName)->getUrl($path, $version);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'assets';
    }
}
