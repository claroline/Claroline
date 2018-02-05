<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

use Claroline\InstallationBundle\Bundle\InstallableBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Base class of all the plugin bundles on the claroline platform.
 */
abstract class PluginBundle extends InstallableBundle implements PluginBundleInterface
{
    public function getBundleFQCN()
    {
        $vendor = $this->getVendorName();
        $bundle = $this->getBundleName();

        return "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
    }

    public function getShortName()
    {
        return $this->getVendorName().$this->getBundleName();
    }

    final public function getVendorName()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[0];
    }

    final public function getBundleName()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[1];
    }

    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        if (file_exists($routingFile = $this->getPath().'/Resources/config/routing.yml')) {
            $config->addRoutingResource($routingFile, null, strtolower($this->getName()));
        }

        return $config;
    }

    /**
     * Deprecated: use getConfiguration instead.
     *
     * @deprecated
     */
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = $this->getPath().$ds.'Resources'.$ds.'config'.$ds.'routing.yml';

        if (file_exists($path)) {
            return [$path];
        }

        return [];
    }

    /**
     * Deprecated: use getConfiguration instead.
     *
     * @deprecated
     */
    public function getRoutingPrefix()
    {
        $vendor = $this->getVendorName();
        $prefix = $this->getBundleName();
        $pattern = '#^(.+)Bundle$#';

        if (preg_match($pattern, $prefix, $matches)) {
            $prefix = $matches[1];
        }

        $prefix = strtolower("{$vendor}_{$prefix}");

        return $prefix;
    }

    public function getConfigFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $defaultFilePath = $this->getPath().$ds.'Resources'.$ds.'config'.$ds.'config.yml';

        if (file_exists($defaultFilePath)) {
            return $defaultFilePath;
        }
    }

    public function getImgFolder()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = "{$this->getPath()}{$ds}Resources{$ds}public{$ds}images{$ds}icons";

        if (is_dir($path)) {
            return $path;
        }
    }

    public function getAssetsFolder()
    {
        return strtolower(str_replace('Bundle', '', $this->getVendorName().$this->getBundleName()));
    }

    /**
     * Returns the list of PHP extensions required by this plugin.
     *
     * Example: ['ldap', 'zlib']
     *
     * @return array
     */
    public function getRequiredExtensions()
    {
        return [];
    }

    /**
     * Returns the list of Claroline plugins required by this plugin. Each plugin
     * in the list must be represented by its fully qualified namespace.
     *
     * @return array
     */
    public function getRequiredPlugins()
    {
        return [];
    }

    /**
     * Returns the list of extra requirements to be met before enabling the plugin.
     *
     * Each requirement must be an array containing the two following keys:
     *
     *   - "test":          An anonymous function checking that the requirement is met.
     *                      Must return true if the check is successful, false otherwise.
     *   - "failure_msg":   A text indicating what went wrong if the test has failed.
     *
     * @return array
     */
    public function getExtraRequirements()
    {
        return [];
    }

    /**
     * Returns true if the plugin has to be activated by default.
     *
     * @return bool
     */
    public function isActiveByDefault()
    {
        return true;
    }

    /**
     * Returns true if the plugin has to be hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Returns the version file path.
     *
     * @return bool
     */
    public function getVersionFilePath()
    {
        return null;
    }
}
