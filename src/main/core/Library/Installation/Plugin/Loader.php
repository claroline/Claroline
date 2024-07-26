<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\KernelBundle\Bundle\PluginBundleInterface;

/**
 * The plugin loader is used to instantiate a plugin bundle class (in order to
 * perform checks, access some of its methods, etc.) while it is not yet
 * known by the application kernel.
 *
 * @todo Remove this class or move it to the installation bundle (only used in tests)
 */
class Loader
{
    public const NO_PLUGIN_FOUND = 0;
    public const NON_EXISTENT_BUNDLE_CLASS = 1;
    public const NON_INSTANTIABLE_BUNDLE_CLASS = 2;
    public const UNEXPECTED_BUNDLE_TYPE = 3;

    /**
     * Searches a plugin bundle by its FQCN and returns an instance of it.
     */
    public function load(string $pluginFqcn, string $pluginPath = null): PluginBundleInterface
    {
        if (!$pluginPath) {
            $rPlugin = new \ReflectionClass($pluginFqcn);
            $pluginPath = $rPlugin->getFileName();
        }

        if (!file_exists($pluginPath)) {
            throw new \RuntimeException("No bundle class file matches the FQCN '{$pluginFqcn}' (expected path was : {$pluginPath})", self::NO_PLUGIN_FOUND);
        }

        return $this->getPluginInstance($pluginPath, $pluginFqcn);
    }

    private function getPluginInstance(string $pluginPath, string $pluginFqcn): PluginBundleInterface
    {
        require_once $pluginPath;

        if (!class_exists($pluginFqcn)) {
            throw new \RuntimeException("Class '{$pluginFqcn}' not found in '{$pluginPath}'.", self::NON_EXISTENT_BUNDLE_CLASS);
        }

        $reflectionClass = new \ReflectionClass($pluginFqcn);

        if (!$reflectionClass->IsInstantiable()) {
            throw new \RuntimeException("Class '{$pluginFqcn}' is not instantiable.", self::NON_INSTANTIABLE_BUNDLE_CLASS);
        }

        if (!$reflectionClass->implementsInterface(PluginBundleInterface::class)) {
            throw new \RuntimeException("Class '{$pluginFqcn}' doesn't implement Claroline 'PluginBundleInterface' interface.", self::UNEXPECTED_BUNDLE_TYPE);
        }

        return new $pluginFqcn();
    }
}
