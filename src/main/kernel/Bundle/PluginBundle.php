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

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
    }

    public function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routingFile = $this->getPath().'/Resources/config/routing.yml';
        if (file_exists($routingFile)) {
            $routes->import($routingFile);
        }
    }

    public function getConfigFile(): ?string
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
     */
    public function getRequiredExtensions(): array
    {
        return [];
    }

    /**
     * Returns the list of Claroline plugins required by this plugin. Each plugin
     * in the list must be represented by its fully qualified namespace.
     */
    public function getRequiredPlugins(): array
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
     */
    public function getExtraRequirements(): array
    {
        return [];
    }

    public function getRequiredBundles(string $environment): array
    {
        return [];
    }
}
