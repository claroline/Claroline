<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class Validator
{
    private $pluginDirectory;

    public function __construct($pluginDirectory)
    {
        if (! is_dir($pluginDirectory))
        {
            throw new ValidationException("'{$pluginDirectory}' is not a valid directory.",
                                          ValidationException::INVALID_PLUGIN_DIR);
        }

        $this->pluginDirectory = $pluginDirectory;
    }

    public function check($pluginFQCN)
    {
        $this->checkPluginFQCNFollowsConventions($pluginFQCN);
        $this->checkPluginFQCNMatchesAnExistingPluginDirectoryStructure($pluginFQCN);
        $this->checkPluginBundleClassIsLoadable($pluginFQCN);
        $this->checkPluginBundleClassExtendsClarolinePlugin($pluginFQCN);
        $this->checkPluginClassReturnsValidRoutingValue($pluginFQCN);
    }

    private function checkPluginFQCNFollowsConventions($pluginFQCN)
    {
        $nameParts = explode('\\', $pluginFQCN);

        if (count($nameParts) == 3)
        {
            if ($nameParts[2] == $nameParts[0] . $nameParts[1])
            {
                return;
            }
        }

        throw new ValidationException("Invalid plugin FQCN '{$pluginFQCN}' : must be of "
                                    . "the type 'Vendor\BundleName\VendorBundleName'.",
                                      ValidationException::INVALID_FQCN);
    }

    private function checkPluginFQCNMatchesAnExistingPluginDirectoryStructure($pluginFQCN)
    {
        $nameParts = explode('\\', $pluginFQCN);

        $vendor = $nameParts[0];
        $bundleName = $nameParts[1];

        $expectedVendorDir = "{$this->pluginDirectory}/{$vendor}";
        $expectedPluginBundleDir = "{$expectedVendorDir}/{$bundleName}";

        if (! is_dir($expectedVendorDir))
        {
            throw new ValidationException("No vendor directory matches FQCN '{$pluginFQCN}' "
                                        . "(expected directory : {$expectedVendorDir}).",
                                          ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }

        if (! is_dir($expectedPluginBundleDir))
        {
            throw new ValidationException("No bundle directory matches FQCN '{$pluginFQCN}' "
                                        . "(expected directory : {$expectedPluginBundleDir}).",
                                          ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }
    }

    private function checkPluginBundleClassIsLoadable($pluginFQCN)
    {
        $expectedClassFile = $this->pluginDirectory . '/'
                           . str_replace('\\', '/', $pluginFQCN) . '.php';

        if (! file_exists($expectedClassFile))
        {
            throw new ValidationException("No plugin class file matches FQCN '{$pluginFQCN}' "
                                        . "(expected class file : {$expectedClassFile})",
                                          ValidationException::INVALID_PLUGIN_CLASS_FILE);
        }

        require_once $expectedClassFile;

        if (! class_exists($pluginFQCN))
        {
            throw new ValidationException("Class '{$pluginFQCN}' not found in '{$expectedClassFile}'.",
                                          ValidationException::INVALID_PLUGIN_CLASS);
        }
    }

    private function checkPluginBundleClassExtendsClarolinePlugin($pluginFQCN)
    {
        $claroPluginClass = 'Claroline\PluginBundle\AbstractType\ClarolinePlugin';

        if (! is_a(new $pluginFQCN, $claroPluginClass))
        {
            throw new ValidationException("Class '{$pluginFQCN}' doesn't extend {$claroPluginClass}.",
                                          ValidationException::INVALID_PLUGIN_TYPE);
        }
    }

    private function checkPluginClassReturnsValidRoutingValue($pluginFQCN)
    {
        $plugin = new $pluginFQCN;
        $paths = $plugin->getRoutingResourcesPaths();

        if (null === $paths)
        {
            return;
        }

        foreach ((array) $paths as $path)
        {
            if (! file_exists($path))
            {
                throw new ValidationException("Cannot find routing file '{$path}'.",
                                              ValidationException::INVALID_ROUTING_RESOURCES);
            }

            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException("Unsupported '{$ext}' extension (use .yml).",
                                              ValidationException::INVALID_ROUTING_RESOURCES);
            }
        }
    }
}