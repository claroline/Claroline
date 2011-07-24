<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class Validator
{
    public function check($pluginFQCN, $pluginDirectory)
    {
        $pluginClassFile = $pluginDirectory
                         . DIRECTORY_SEPARATOR
                         . str_replace('\\', DIRECTORY_SEPARATOR, $pluginFQCN)
                         . '.php';
        
        $this->checkPluginDirectoryExists($pluginDirectory);
        $this->checkPluginClassFileExists($pluginClassFile, $pluginFQCN);
        $this->checkPluginClassIsLoadable($pluginClassFile, $pluginFQCN);
        $this->checkPluginClassExtendsClarolinePlugin($pluginClassFile, $pluginFQCN);
        $this->checkPluginClassReturnsValidRoutingValue($pluginFQCN);
    }

    private function checkPluginDirectoryExists($pluginDirectory)
    {
        if (! is_dir($pluginDirectory))
        {
            throw new ValidationException("'{$pluginDirectory}' is not valid plugin directory.");
        }
    }

    private function checkPluginClassFileExists($pluginClassFile, $pluginFQCN)
    {
        if (! file_exists($pluginClassFile))
        {
            throw new ValidationException("Plugin '{$pluginFQCN}' should be defined in '"
                                        . "{$pluginClassFile}' : file not found.");
        }
    }

    private function checkPluginClassIsLoadable($pluginClassFile, $pluginFQCN)
    {
        require_once $pluginClassFile;

        if (! class_exists($pluginFQCN))
        {
            throw new ValidationException("Class '{$pluginFQCN}' not found in "
                                        . "'{$pluginClassFile}'.");
        }
    }

    private function checkPluginClassExtendsClarolinePlugin($pluginClassFile, $pluginFQCN)
    {
        require_once $pluginClassFile;

        $claroPluginClass = 'Claroline\PluginBundle\AbstractType\ClarolinePlugin';

        if (! is_a(new $pluginFQCN, $claroPluginClass))
        {
            throw new ValidationException("Class '{$pluginFQCN}' doesn't extend {$claroPluginClass}.");
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
                throw new ValidationException("Cannot find file '{$path}'.");
            }

            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException("Unsupported '{$ext}' extension (use .yml).");
            }
        }
    }
}