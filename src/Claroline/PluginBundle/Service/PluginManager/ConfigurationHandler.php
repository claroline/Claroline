<?php

namespace Claroline\PluginBundle\Service\PluginManager;

class ConfigurationHandler
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
        $this->doAddItem($this->pluginNamespacesFile, $namespace, 'Namespace');
    }

    public function removeNamespace($namespace)
    {
        $this->doRemoveItem($this->pluginNamespacesFile, $namespace);
    }

    public function addInstantiableBundle($bundleFQCN)
    {
        $this->doAddItem($this->pluginBundlesFile, $bundleFQCN, 'Bundle FQCN');
    }

    public function removeInstantiableBundle($bundleFQCN)
    {
        $this->doRemoveItem($this->pluginBundlesFile, $bundleFQCN);
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

    private function doAddItem($file, $item, $itemType)
    {
        if (empty($item))
        {
            throw new \Exception("{$itemType} argument cannot be empty.");
        }

        $items = file($file, FILE_IGNORE_NEW_LINES);

        if (! in_array($item, $items))
        {
            $items[] = $item;
            file_put_contents($file, implode("\n", $items));
        }
    }

    private function doRemoveItem($file, $item)
    {
        $items = file($file, FILE_IGNORE_NEW_LINES);

        foreach ($items as $key => $value)
        {
            if ($value === $item)
            {
                unset($items[$key]);
            }
        }

        file_put_contents($file, implode("\n", $items));
    }
}