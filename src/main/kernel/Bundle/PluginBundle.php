<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Bundle;

use Claroline\InstallationBundle\Bundle\InstallableBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Base class of all the plugin bundles on the claroline platform.
 */
abstract class PluginBundle extends InstallableBundle implements PluginBundleInterface
{
    public function supports(string $environment): bool
    {
        return true;
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
    }

    public function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routingFile = $this->getPath().'/Resources/config/routing.yml';
        if (file_exists($routingFile)) {
            $routes->import($routingFile);
        }
    }

    public function getConfigFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $defaultFilePath = $this->getPath().$ds.'Resources'.$ds.'config'.$ds.'config.yml';

        if (file_exists($defaultFilePath)) {
            return $defaultFilePath;
        }

        return null;
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
     * Returns path to the folder of the icon sets for resources.
     *
     * @return string
     */
    public function getResourcesIconsSetsFolder()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = "{$this->getPath()}{$ds}Resources{$ds}public{$ds}images{$ds}resources{$ds}icons";

        if (is_dir($path)) {
            return $path;
        }

        return null;
    }

    public function getRequiredBundles(string $environment): array
    {
        return [];
    }

    public function getDescription()
    {
        return file_exists($this->getPath().'/DESCRIPTION.md') ? file_get_contents($this->getPath().'/DESCRIPTION.md') : '';
    }
}
