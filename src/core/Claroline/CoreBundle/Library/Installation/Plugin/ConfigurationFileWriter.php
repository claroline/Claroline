<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \InvalidArgumentException;
use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This class is used to (un-)register the namespace, the bundle name and the
 * routing resources of a plugin in the application configuration files,
 * in order to have it correctly instantiated by the kernel.
 *
 * @DI\Service("claroline.plugin.recorder_configuration_file_writer")
 */
class ConfigurationFileWriter
{
    private $pluginNamespacesFile;
    private $pluginBundlesFile;
    private $pluginRoutingFile;
    private $yamlHandler;

    /**
     * Constructor.
     *
     * @param string $pluginNamespacesFile
     * @param string $pluginBundlesFile
     * @param string $pluginRoutingFile
     * @param Yaml   $yamlHandler
     *
     * @DI\InjectParams({
     *     "pluginNamespacesFile" = @DI\Inject("%claroline.param.plugin_namespaces_file%"),
     *     "pluginBundlesFile" = @DI\Inject("%claroline.param.plugin_bundles_file%"),
     *     "pluginRoutingFile" = @DI\Inject("%claroline.param.plugin_routing_file%"),
     *     "yamlHandler" = @DI\Inject("symfony.yaml")
     * })
     */
    public function __construct(
        $pluginNamespacesFile,
        $pluginBundlesFile,
        $pluginRoutingFile,
        Yaml $yamlHandler
    )
    {
        $this->setPluginNamespacesFile($pluginNamespacesFile);
        $this->setPluginBundlesFile($pluginBundlesFile);
        $this->setPluginRoutingFile($pluginRoutingFile);
        $this->yamlHandler = $yamlHandler;
    }

    /**
     * Sets the plugin namespace file.
     *
     * @param string $filePath
     */
    public function setPluginNamespacesFile($filePath)
    {
        $this->assertFileIsWriteable($filePath);
        $this->pluginNamespacesFile = $filePath;
    }

    /**
     * Sets the plugin bundle file.
     *
     * @param string $filePath
     */
    public function setPluginBundlesFile($filePath)
    {
        $this->assertFileIsWriteable($filePath);
        $this->pluginBundlesFile = $filePath;
    }

    /**
     * Sets the plugin routing file.
     *
     * @param string $filePath
     */
    public function setPluginRoutingFile($filePath)
    {
        $this->assertFileIsWriteable($filePath);
        $this->pluginRoutingFile = $filePath;
    }

    /**
     * Registers a namespace in the plugin namespace file.
     *
     * @param string $namespace
     */
    public function registerNamespace($namespace)
    {
        $this->doAddItem($this->pluginNamespacesFile, $namespace, 'Namespace');
    }

    /**
     * Removes a namespace from the plugin namespace file.
     *
     * @param string $namespace
     */
    public function removeNamespace($namespace)
    {
        if (!in_array($namespace, $this->getSharedVendorNamespaces())) {
            $this->doRemoveItem($this->pluginNamespacesFile, $namespace);
        }
    }

    /**
     * Registers a bundle in the plugin bundles file.
     *
     * @param string $pluginFqcn
     */
    public function addInstantiableBundle($pluginFqcn)
    {
        $this->doAddItem($this->pluginBundlesFile, $pluginFqcn, 'Plugin FQCN');
    }

    /**
     * Removes a bundle from the plugin bundles file.
     *
     * @param string $pluginFqcn
     */
    public function removeInstantiableBundle($pluginFqcn)
    {
        $this->doRemoveItem($this->pluginBundlesFile, $pluginFqcn);
    }

    /**
     * Registers new routing resources in the plugin routing file.
     *
     * @param string $pluginFqcn
     * @param mixed  $paths
     * @param string $prefix
     */
    public function importRoutingResources($pluginFqcn, $paths, $prefix)
    {
        $nameParts = explode('\\', $pluginFqcn);
        $vendor = $nameParts[0];
        $bundleName = $nameParts[1];
        $bundleClassName = $nameParts[2];
        $resources = array();

        foreach ((array) $paths as $pathKey => $path) {
            // extract resource path relatively to the plugin's bundle
            $ds = preg_quote(DIRECTORY_SEPARATOR);
            $pattern = "#^(.+){$ds}{$vendor}{$ds}{$bundleName}{$ds}(.+)$#";
            preg_match($pattern, $path, $matches);
            $relativePath = $matches[2];
            // replace os-dependant directory separator by forward slash (symfony convention)
            $normalizedPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            // build a key for the resource ('$pathKey' index allowing several resources per bundle)
            $key = "{$bundleClassName}_{$pathKey}";
            // express the path in the symfony way
            $value = "@{$bundleClassName}/{$normalizedPath}";
            // build the entry to be dumped in the yaml routing file
            $resources[$key] = array(
                'resource' => $value,
                'prefix' => $prefix
            );
        }

        $resources = array_merge($this->getRoutingResources(), $resources);
        $yaml = $this->yamlHandler->dump($resources);

        file_put_contents($this->pluginRoutingFile, $yaml);
    }

    /**
     * Removes routing resources from the plugin routing file.
     *
     * @param string $pluginFqcn
     */
    public function removeRoutingResources($pluginFqcn)
    {
        $nameParts = explode('\\', $pluginFqcn);
        $className = $nameParts[2];
        $resources = $this->yamlHandler->parse($this->pluginRoutingFile);

        foreach (array_keys((array) $resources) as $key) {
            if (substr($key, 0, strlen($className)) == $className) {
                unset($resources[$key]);
            }
        }

        $yaml = $this->yamlHandler->dump($resources);
        file_put_contents($this->pluginRoutingFile, $yaml);
    }

    /**
     * Checks if a plugin is registered in the application configuration files.
     *
     * @param string $pluginFqcn
     *
     * @return boolean
     */
    public function isRecorded($pluginFqcn)
    {
        $namespaceParts = explode('\\', $pluginFqcn);
        $vendorNamespace = array_shift($namespaceParts);

        $isNamespaceRegistered = in_array(
            $vendorNamespace, $this->getRegisteredNamespaces()
        );
        $isBundleRegistered = in_array(
            $pluginFqcn, $this->getRegisteredBundles()
        );

        if ($isNamespaceRegistered && $isBundleRegistered) {
            return true;
        }

        return false;
    }

    private function assertFileIsWriteable($file)
    {
        if (!file_exists($file)) {
            if (!touch($file)) {
                throw new RuntimeException("File '{$file}' not found.");
            }
        }

        if (!is_writable($file)) {
            throw new RuntimeException("File '{$file}' is not writable.");
        }
    }

    private function getRegisteredBundles()
    {
        return file($this->pluginBundlesFile, FILE_IGNORE_NEW_LINES);
    }

    private function getRegisteredNamespaces()
    {
        return file($this->pluginNamespacesFile, FILE_IGNORE_NEW_LINES);
    }

    private function getSharedVendorNamespaces()
    {
        $vendors = array();

        foreach ($this->getRegisteredBundles() as $bundleName) {
            $nameParts = explode('\\', $bundleName);
            $vendors[] = $nameParts[0];
        }

        $uniqueVendors = array_unique($vendors);
        $sharedVendors = array();

        foreach ($uniqueVendors as $uniqueVendor) {
            $vendorCount = 0;

            foreach ($vendors as $vendor) {
                if ($vendor == $uniqueVendor) {
                    $vendorCount++;
                    if ($vendorCount > 1) {
                        $sharedVendors[] = $vendor;
                    }
                }
            }
        }

        return $sharedVendors;
    }

    private function getRoutingResources()
    {
        $resources = $this->yamlHandler->parse($this->pluginRoutingFile);

        return (array) $resources;
    }

    private function doAddItem($file, $item, $itemType)
    {
        if (empty($item)) {
            throw new InvalidArgumentException(
                "{$itemType} argument cannot be empty."
            );
        }

        $items = file($file, FILE_IGNORE_NEW_LINES);

        if (!in_array($item, $items)) {
            $items[] = $item;
            file_put_contents($file, implode("\n", $items));
        }
    }

    private function doRemoveItem($file, $item)
    {
        $items = file($file, FILE_IGNORE_NEW_LINES);

        foreach ($items as $key => $value) {
            if ($value === $item) {
                unset($items[$key]);
            }
        }

        file_put_contents($file, implode("\n", $items));
    }
}
