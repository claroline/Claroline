<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;
use Claroline\PluginBundle\Service\PluginManager\ApplicationValidator;
use Claroline\PluginBundle\Service\PluginManager\ToolValidator;

class Validator
{
    // Dependencies
    private $pluginDirectory;
    private $yamlParser;
    private $applicationValidator;
    private $toolValidator;

    // Attributes caching values between private checks
    private $pluginFQCNParts;
    private $pluginInstance;
    
    // FQCN of the plugin currently being checked
    private $pluginFQCN;

    public function __construct($pluginDirectory, Parser $yamlParser)
    {
        $this->setPluginDirectory($pluginDirectory);
        $this->yamlParser = $yamlParser;
    }

    public function setPluginDirectory($pluginDirectory)
    {
        if (! is_dir($pluginDirectory))
        {
            throw new ValidationException(
                "'{$pluginDirectory}' is not a valid directory.",
                ValidationException::INVALID_PLUGIN_DIR);
        }

        $this->pluginDirectory = $pluginDirectory;
    }
    
    public function setApplicationValidator(ApplicationValidator $validator)
    {
        $this->applicationValidator = $validator;
    }

    public function setToolValidator(ToolValidator $validator)
    {
        $this->toolValidator = $validator;
    }
    
    public function check($pluginFQCN)
    {
        $this->pluginFQCN = $pluginFQCN;
        $this->resetCachedValues();
        $this->checkFQCNConvention();
        $this->checkDirectoryStructure();
        $this->checkIsLoadable();
        $this->checkExtendsClarolinePlugin();
        $this->checkRoutingResources();
        $this->checkTranslationKeys();
        $this->checkSubType();
    }

    private function resetCachedValues()
    {
        $this->pluginFQCNParts = null;
        $this->pluginInstance = null;
    }

    private function explodeFQCN($pluginFQCN)
    {
        $nameParts = explode('\\', $pluginFQCN);

        if (count($nameParts) == 3)
        {
            if ($nameParts[2] == $nameParts[0] . $nameParts[1])
            {
                $pluginFQCNParts['vendor'] = $nameParts[0];
                $pluginFQCNParts['bundle'] = $nameParts[1];
                $pluginFQCNParts['class'] = $nameParts[2];

                return $pluginFQCNParts;
            }
        }

        return false;
    }

    private function getPluginFQCNParts()
    {
        if ($this->pluginFQCNParts === null)
        {
            // This will initialize the pluginFQCNParts
            // attribute if no exception is thrown
            $this->checkFQCNConvention();
        }
        
        return $this->pluginFQCNParts;
    }

    private function getPluginInstance()
    {
        if ($this->pluginInstance === null)
        {
            // This will initialize the pluginInstance
            // attribute if no exception is thrown
            $this->checkIsLoadable();
        }
        
        return $this->pluginInstance;
    }

    private function checkFQCNConvention()
    {
        $nameParts = $this->explodeFQCN($this->pluginFQCN);

        if ($nameParts === false)
        {
            throw new ValidationException(
                "Plugin FQCN '{$this->pluginFQCN}' doesn't follow the "
                . "'Vendor\BundleName\VendorBundleName' convention.",
                ValidationException::INVALID_FQCN);
        }

        // Caches FQCN parts for subsequent checks
        $this->pluginFQCNParts = $nameParts;
    }

    private function checkDirectoryStructure()
    {
        $nameParts = $this->getPluginFQCNParts();

        $expectedVendorDir = $this->pluginDirectory
                . DIRECTORY_SEPARATOR
                . $nameParts['vendor'];
        $expectedPluginBundleDir = $expectedVendorDir
                . DIRECTORY_SEPARATOR
                . $nameParts['bundle'];

        if (! is_dir($expectedVendorDir))
        {
            throw new ValidationException(
                "No vendor directory matches FQCN '{$this->pluginFQCN}' "
                . "(expected directory : {$expectedVendorDir}).",
                ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }

        if (! is_dir($expectedPluginBundleDir))
        {
            throw new ValidationException(
                "No bundle directory matches FQCN '{$this->pluginFQCN}' "
                . "(expected directory : {$expectedPluginBundleDir}).",
                ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }
    }

    private function checkIsLoadable()
    {
        $nameParts = $this->getPluginFQCNParts();

        $expectedClassFile = $this->pluginDirectory 
                . DIRECTORY_SEPARATOR
                . $nameParts['vendor']
                . DIRECTORY_SEPARATOR
                . $nameParts['bundle']
                . DIRECTORY_SEPARATOR
                . $nameParts['class']
                . '.php';

        if (! file_exists($expectedClassFile))
        {
            throw new ValidationException(
                "No plugin class file matches FQCN '{$this->pluginFQCN}' "
                . "(expected class file : {$expectedClassFile})",
                ValidationException::INVALID_PLUGIN_CLASS_FILE);
        }

        require_once $expectedClassFile;

        if (! class_exists($this->pluginFQCN))
        {
            throw new ValidationException(
                "Class '{$this->pluginFQCN}' not found in '{$expectedClassFile}'.",
                ValidationException::INVALID_PLUGIN_CLASS);
        }

        // Caches plugin instance for subsequent checks
        $this->pluginInstance = new $this->pluginFQCN;
    }

    private function checkExtendsClarolinePlugin()
    {
        $pluginInstance = $this->getPluginInstance();
        $claroPluginClass = 'Claroline\PluginBundle\AbstractType\ClarolinePlugin';
        
        if (! is_a($pluginInstance, $claroPluginClass))
        {
            throw new ValidationException(
                "Class '{$this->pluginFQCN}' doesn't extend '{$claroPluginClass}'.",
                ValidationException::INVALID_PLUGIN_TYPE);
        }
    }
 
    private function checkRoutingResources()
    {
        $plugin = $this->getPluginInstance();
        $paths = $plugin->getRoutingResourcesPaths();

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
                    ValidationException::INVALID_ROUTING_PATH);
            }

            $requiredLocation = realpath($plugin->getPath());

            // Checks that the provided resource path starts with the bundle one
            // (i.e. that the resource file is located within the plugin directory)
            if (substr($path, 0, strlen($requiredLocation)) != $requiredLocation)
            {                
                throw new ValidationException(
                    "{$this->pluginFQCN} : Invalid routing file '{$path}' "
                    . "(must be located within the bundle).",
                    ValidationException::INVALID_ROUTING_LOCATION);
            }
            
            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : Unsupported '{$ext}' extension for "
                    . "routing file '{$path}'(use .yml).",
                    ValidationException::INVALID_ROUTING_EXTENSION);
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
                    ValidationException::INVALID_YAML_RESOURCE);
            }
        }
    }

    private function checkTranslationKeys()
    {
        $plugin = $this->getPluginInstance();
        $keys = array();
        $keys['name'] = $plugin->getNameTranslationKey();
        $keys['description'] = $plugin->getDescriptionTranslationKey();

        foreach ($keys as $type => $key)
        {
            if (! is_string($key))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : {$type} translation key must be a string.",
                    ValidationException::INVALID_TRANSLATION_KEY);
            }

            if (empty($key))
            {
                throw new ValidationException(
                    "{$this->pluginFQCN} : {$type} translation key cannot be empty.",
                    ValidationException::INVALID_TRANSLATION_KEY);
            }
        }
    }

    private function checkSubType()
    {
        $plugin = $this->getPluginInstance();

        if (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineApplication'))
        {
            $this->applicationValidator->check($plugin);
        }
        elseif (is_a($plugin, 'Claroline\PluginBundle\AbstractType\ClarolineTool'))
        {
            $this->toolValidator->check($plugin);
        }
    }
}