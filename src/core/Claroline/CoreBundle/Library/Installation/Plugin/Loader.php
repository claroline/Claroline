<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * The plugin loader is used to instantiate a plugin bundle class (in order to
 * perform checks, access some of its methods, etc.) while it is not yet
 * known by the application kernel.
 *
 * @DI\Service("claroline.plugin.loader")
 */
class Loader
{
    const NO_PLUGIN_FOUND = 0;
    const NON_EXISTENT_BUNDLE_CLASS = 1;
    const NON_INSTANTIABLE_BUNDLE_CLASS = 2;
    const UNEXPECTED_BUNDLE_TYPE = 3;

    private $pluginDirectory;

    /**
     * Constructor.
     *
     * @param string $pluginDirectory
     *
     * @DI\InjectParams({
     *     "pluginDirectory" = @DI\Inject("%claroline.param.plugin_directory%")
     * })
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * Searches a plugin bundle by its FQCN and returns an instance of it.
     *
     * @param string $pluginFqcn
     * @throws RuntimeException if the plugin class cannot be found or instantiated
     *
     * @return PluginBundle
     */
    public function load($pluginFqcn)
    {
        $pluginPath = $this->pluginDirectory
            . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, $pluginFqcn)
            . '.php';

        if (!file_exists($pluginPath)) {
            throw new RuntimeException(
                "No bundle class file matches the FQCN '{$pluginFqcn}' "
                . '(expected path was : ' . $pluginPath . ')',
                self::NO_PLUGIN_FOUND
            );
        }

        return $this->getPluginInstance($pluginPath, $pluginFqcn);
    }

    private function getPluginInstance($pluginPath, $pluginFqcn)
    {
        require_once $pluginPath;

        if (!class_exists($pluginFqcn)) {
            throw new RuntimeException(
                "Class '{$pluginFqcn}' not found in '{$pluginPath}'.",
                self::NON_EXISTENT_BUNDLE_CLASS
            );
        }

        $reflectionClass = new \ReflectionClass($pluginFqcn);

        if (!$reflectionClass->IsInstantiable()) {
            throw new RuntimeException(
                "Class '{$pluginFqcn}' is not instantiable.",
                self::NON_INSTANTIABLE_BUNDLE_CLASS
            );
        }

        if (!$reflectionClass->isSubclassOf('Claroline\CoreBundle\Library\PluginBundle')) {
            throw new RuntimeException(
                "Class '{$pluginFqcn}' doesn't extend Claroline 'PluginBundle' class.",
                self::UNEXPECTED_BUNDLE_TYPE
            );
        }

        return new $pluginFqcn;
    }
}