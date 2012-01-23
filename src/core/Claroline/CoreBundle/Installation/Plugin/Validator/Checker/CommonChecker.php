<?php

namespace Claroline\CoreBundle\Installation\Plugin\Validator\Checker;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Config\FileLocatorInterface;
use Claroline\CoreBundle\Plugin\ClarolinePlugin;
use Claroline\CoreBundle\Plugin\ClarolineExtension;
use Claroline\CoreBundle\Plugin\ClarolineTool;
use Claroline\CoreBundle\Exception\ValidationException;

class CommonChecker
{
    private $plugin;
    private $pluginFQCN;
    private $routingFilePath;
    private $pluginDirectories;
    private $yamlParser;
    private $fileLocator;
    
    public function __construct(
        $pluginRoutingFilePath,
        array $pluginDirectories,
        Yaml $yamlParser,
        FileLocatorInterface $fileLocator
    )
    {        
        $this->setPluginRoutingFilePath($pluginRoutingFilePath);
        $this->setPluginDirectories($pluginDirectories);
        $this->yamlParser = $yamlParser;
        $this->fileLocator = $fileLocator;
    }

    public function setPluginDirectories(array $directories)
    {
        $this->pluginDirectories = $directories;
    }
    
    public function setPluginRoutingFilePath($path)
    {
        $this->routingFilePath = $path;
    }
    
    public function setFileLocator($fileLocator) 
    {
        $this->fileLocator = $fileLocator;
    }
        
    public function check(ClarolinePlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->pluginFQCN = get_class($plugin);
        
        $this->checkPluginFollowsFQCNConvention();
        $this->checkPluginExtendsClarolinePluginSubType();
        $this->checkPluginIsInTheRightSubDirectory();
        $this->checkRoutingPrefixIsValid();
        $this->checkRoutingPrefixIsNotAlreadyRegistered();
        $this->checkRoutingResourcesAreLoadable();
        $this->checkTranslationKeysAreValid();
    }
    
    private function checkPluginFollowsFQCNConvention()
    {
        $nameParts = explode('\\', $this->pluginFQCN);
        
        if (count($nameParts) !== 3 || $nameParts[2] !== $nameParts[0] . $nameParts[1])
        {
            throw new ValidationException(
                "Plugin FQCN '{$this->pluginFQCN}' doesn't follow the "
                . "'Vendor\BundleName\VendorBundleName' convention.",
                ValidationException::INVALID_FQCN
            );
        }
    }
    
    private function checkPluginExtendsClarolinePluginSubType()
    {
        if (! $this->plugin instanceof \Claroline\CoreBundle\Plugin\ClarolineExtension
            && ! $this->plugin instanceof \Claroline\CoreBundle\Plugin\ClarolineTool)
        {
            throw new ValidationException(
                "Class '{$this->pluginFQCN}' must inherit one of the ClarolinePlugin "
                . "sub-types (ClarolineExtension, ClarolineApplication or ClarolineTool).",
                ValidationException::INVALID_PLUGIN_TYPE
            );
        }
    }
    
    private function checkPluginIsInTheRightSubDirectory()
    {        
        if ($this->plugin instanceof ClarolineExtension)
        {
            $expectedDirectory = $this->pluginDirectories['extension'];
        }
        elseif ($this->plugin instanceof ClarolineTool)
        {
            $expectedDirectory = $this->pluginDirectories['tool'];
        }
        
        $expectedDirectory = realpath($expectedDirectory);       
        $expectedDirectoryEscaped = preg_quote($expectedDirectory, '/');
        $pluginPath = realpath($this->plugin->getPath());
        
        if (preg_match("/^{$expectedDirectoryEscaped}/", $pluginPath) === 0)
        {
            throw new ValidationException(
                "Plugin '{$this->pluginFQCN}' location doesn't match its "
                . "type (expected location was {$expectedDirectory}).",
                ValidationException::INVALID_PLUGIN_LOCATION
            );
        }
    }
    
    private function checkRoutingPrefixIsValid()
    {
        $prefix = $this->plugin->getRoutingPrefix();
        
        if (! is_string($prefix))
        {
            throw new ValidationException(
                "{$this->pluginFQCN} : routing prefix must be a string.",
                ValidationException::INVALID_ROUTING_PREFIX
            );
        }
        
        if (empty($prefix))
        {
            throw new ValidationException(
                "{$this->pluginFQCN} : routing prefix cannot be empty.",
                ValidationException::INVALID_ROUTING_PREFIX
            );
        }
        
        if (preg_match('#\s#', $prefix))
        {
            throw new ValidationException(
                "{$this->pluginFQCN} : routing prefix cannot contain white spaces.",
                ValidationException::INVALID_ROUTING_PREFIX
            );
        }
    }
    
    private function checkRoutingPrefixIsNotAlreadyRegistered()
    {
        $prefix = $this->plugin->getRoutingPrefix();
        $routingPaths = $this->plugin->getRoutingResourcesPaths();
        $routingResources = (array) $this->yamlParser->parse($this->routingFilePath);
        
        foreach ($routingResources as $resource)
        {
            $isConflicting = ! $this->isOneOfTheFiles($resource['resource'], $routingPaths);
            
            if ($resource['prefix'] === $prefix &&  $isConflicting)
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : routing prefix '{$prefix}' is already registered in another plugin.",
                    ValidationException::INVALID_ALREADY_REGISTERED_PREFIX
                );
            }
        }
    }
    
    private function isOneOfTheFiles($resource, $paths)
    {
        if (! is_array($paths))
        {
            $paths = array($paths);
        }
        
        $realpath = $this->fileLocator->locate($resource);
        $realpath = str_replace('\\', '/', $realpath);
        
        foreach ($paths as $path)
        {
            $path = str_replace('\\', '/', $path);
            
            if ($path == $realpath)
            {
                return true;
            }
        }
        
        return false;
    }
    
    private function checkRoutingResourcesAreLoadable()
    {
        $paths = $this->plugin->getRoutingResourcesPaths();

        if ($paths === null)
        {
            return;
        }

        foreach ((array) $paths as $path)
        {
            $path = realpath($path);

            if (! file_exists($path))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : Cannot find routing file '{$path}'.",
                    ValidationException::INVALID_ROUTING_PATH
                );
            }

            $bundlePath = preg_quote(realpath($this->plugin->getPath()), '/');
            
            if (preg_match("/^{$bundlePath}/", $path) === 0)
            {                
                throw new ValidationException(
                    "{$this->pluginFQCN} : Invalid routing file '{$path}' "
                    . "(must be located within the bundle).",
                    ValidationException::INVALID_ROUTING_LOCATION
                );
            }
            
            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : Unsupported '{$ext}' extension for "
                    . "routing file '{$path}'(use .yml).",
                    ValidationException::INVALID_ROUTING_EXTENSION
                );
            }

            try
            {
                $yamlString = file_get_contents($path);
                $this->yamlParser->parse($yamlString);
            }
            catch (ParseException $ex)
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : Unloadable YAML routing file "
                    . "(parse exception message : '{$ex->getMessage()}')",
                    ValidationException::INVALID_YAML_RESOURCE
                );
            }
        }
    }

    private function checkTranslationKeysAreValid()
    {
        $keys = array();
        $keys['name'] = $this->plugin->getNameTranslationKey();
        $keys['description'] = $this->plugin->getDescriptionTranslationKey();

        foreach ($keys as $type => $key)
        {
            if (! is_string($key))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : {$type} translation key must be a string.",
                    ValidationException::INVALID_TRANSLATION_KEY
                );
            }

            if (empty($key))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : {$type} translation key cannot be empty.",
                    ValidationException::INVALID_TRANSLATION_KEY
                );
            }
        }
    }
}