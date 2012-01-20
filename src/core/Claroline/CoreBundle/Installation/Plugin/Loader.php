<?php

namespace Claroline\CoreBundle\Installation\Plugin;

use Claroline\CoreBundle\Exception\LoaderException;

class Loader
{
    private $pluginDirectories;
    
    public function __construct(array $pluginDirectories)
    {
        $this->setPluginDirectories($pluginDirectories);
    }
    
    public function setPluginDirectories(array $directories)
    {
        $this->pluginDirectories = $directories;
    }
    
    public function load($pluginFQCN)
    {
        $pluginPath = $this->locate($pluginFQCN);
        
        return $this->getPluginInstance($pluginPath, $pluginFQCN);
    }
    
    private function locate($pluginFQCN)
    {        
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFQCN) . '.php';
        $possiblePaths = array();
        $foundPaths = array();
        
        foreach ($this->pluginDirectories as $directory)
        {
            $possiblePath = $directory . DIRECTORY_SEPARATOR . $relativePath;
            $possiblePaths[] = $possiblePath;
            
            if (file_exists($possiblePath))
            {
                $foundPaths[] = $possiblePath;
            }
        }
        
        $foundPathCount = count($foundPaths);
        
        if ($foundPathCount == 0)
        {
            throw new LoaderException(
                "No bundle class file matches the FQCN '{$pluginFQCN}' "
                . '(possible paths where : ' . implode(', ', $possiblePaths) . ')',
                LoaderException::NO_PLUGIN_FOUND
            );
        }
        
        if ($foundPathCount > 1)
        {
            throw new LoaderException(
                "{$foundPathCount} bundle class files matches the FQCN "
                . "'{$pluginFQCN}' (" . implode(', ', $foundPaths) . ')',
                LoaderException::MULTIPLE_PLUGINS_FOUND
            );
        }
        
        return $foundPaths[0];
    }
    
    private function getPluginInstance($pluginPath, $pluginFQCN)
    {
        require_once $pluginPath;

        if (! class_exists($pluginFQCN))
        {
            throw new LoaderException(
                "Class '{$pluginFQCN}' not found in '{$pluginPath}'.",
                LoaderException::NON_EXISTENT_BUNDLE_CLASS
            );
        }
        
        $reflectionClass = new \ReflectionClass($pluginFQCN);
        
        if (! $reflectionClass->IsInstantiable())
        {
            throw new LoaderException(
                "Class '{$pluginFQCN}' is not instantiable.",
                LoaderException::NON_INSTANTIABLE_BUNDLE_CLASS
            );
        }
        
        return new $pluginFQCN;
    }
}