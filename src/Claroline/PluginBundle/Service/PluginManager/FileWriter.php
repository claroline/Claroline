<?php

namespace Claroline\PluginBundle\Service\PluginManager;

class FileWriter
{
    private $pluginNamespacesFile;
    private $pluginBundlesFile;

    public function __construct($pluginNamespacesFile, $pluginBundlesFile)
    {
        $this->checkFile($pluginNamespacesFile);
        $this->checkFile($pluginBundlesFile);
        $this->pluginNamespacesFile = $pluginNamespacesFile;
        $this->pluginBundlesFile = $pluginBundlesFile;
    }

    public function getRegisteredNamespaces()
    {
        return file($this->pluginNamespacesFile, FILE_IGNORE_NEW_LINES);
    }

    public function getRegisteredBundles()
    {
        return file($this->pluginBundlesFile, FILE_IGNORE_NEW_LINES);
    }

    public function getSharedVendorNamespaces()
    {
        $vendors = array();

        foreach ($this->getRegisteredBundles() as $bundleName)
        {
            $nameParts = explode('\\', $bundleName);
            $vendors[] = $nameParts[0];
        }

        $uniqueVendors = array_unique($vendors);
        $sharedVendors = array();

        foreach ($uniqueVendors as $uniqueVendor)
        {
            $vendorCount = 0;

            foreach ($vendors as $vendor)
            {
                if ($vendor == $uniqueVendor)
                {
                    $vendorCount++;
                    if ($vendorCount > 1)
                    {
                        $sharedVendors[] = $vendor;
                    }
                }
            }
        }

        return $sharedVendors;
    }

    public function registerNamespace($namespace)
    {
        if (empty($namespace))
        {
            throw new \Exception('Namespace argument cannot be empty.');
        }

        $namespaces = file($this->pluginNamespacesFile, FILE_IGNORE_NEW_LINES);
        
        if (! in_array($namespace, $namespaces))
        {
            $namespaces[] = $namespace;
            file_put_contents($this->pluginNamespacesFile, implode("\n", $namespaces));
        }
    }

    public function removeNamespace($namespace)
    {
        $namespaces = file($this->pluginNamespacesFile, FILE_IGNORE_NEW_LINES);

        foreach ($namespaces as $key => $namespaceItem)
        {
            if ($namespaceItem === $namespace)
            {
                unset($namespaces[$key]);
            }
        }
        
        file_put_contents($this->pluginNamespacesFile, implode("\n", $namespaces));
    }

    public function addInstantiableBundle($bundleFQCN)
    {
        if (empty($bundleFQCN))
        {
            throw new \Exception('Bundle FQCN argument cannot be empty.');
        }

        $bundles = file($this->pluginBundlesFile, FILE_IGNORE_NEW_LINES);

        if (! in_array($bundleFQCN, $bundles))
        {
            $bundles[] = $bundleFQCN;
            file_put_contents($this->pluginBundlesFile, implode("\n", $bundles));
        }
    }

    public function removeInstantiableBundle($bundleFQCN)
    {
        $bundles = file($this->pluginBundlesFile, FILE_IGNORE_NEW_LINES);

        foreach ($bundles as $key => $bundle)
        {
            if ($bundle === $bundleFQCN)
            {
                unset($bundles[$key]);
            }
        }

        file_put_contents($this->pluginBundlesFile, implode("\n", $bundles));
    }

    private function checkFile($file)
    {
        if (! file_exists($file))
        {
            throw new \Exception("File '{$file}' not found.");
        }

        if (! is_writable($file))
        {
            throw new \Exception("File '{$file}' is not writable.");
        }
    }

    public function importRoutingResource()
    {

    }

    // no use, keep plugin config in db
    public function importServiceConfiguration()
    {
        
    }

    // No use, use DependencyInjection directory instead
    public function importServiceResource()
    {

    }
}