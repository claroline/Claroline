<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class Validator
{
    // Dependencies
    private $pluginDirectory;
    private $yamlParser;

    // Attributes storing values between private checks
    private $pluginFQCNParts;
    private $pluginInstance;

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

    public function check($pluginFQCN)
    {
        $this->resetCachedValues();
        $this->checkFQCNConvention($pluginFQCN);
        $this->checkDirectoryStructure($pluginFQCN);
        $this->checkIsLoadable($pluginFQCN);
        $this->checkExtendsClarolinePlugin($pluginFQCN);
        $this->checkRoutingResources($pluginFQCN);
        $this->checkTranslationKeys($pluginFQCN);
        $this->checkApplicationConstraints($pluginFQCN);
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

    private function getPluginFQCNParts($pluginFQCN)
    {
        if ($this->pluginFQCNParts === null)
        {
            $this->checkFQCNConvention($pluginFQCN);

            // Should be done yet in the check above (but what if its implementation
            // changes ? Subsequent checks rely on this getter...)
            $this->pluginFQCNParts = $this->explodeFQCN($pluginFQCN);
        }
        
        return $this->pluginFQCNParts;
    }

    private function getPluginInstance($pluginFQCN)
    {
        if ($this->pluginInstance === null)
        {
            $this->checkIsLoadable($pluginFQCN);

            // Should be done yet (same remark as in previous method)
            $this->pluginInstance = new $pluginFQCN;
        }

        return $this->pluginInstance;
    }

    private function checkFQCNConvention($pluginFQCN)
    {
        $nameParts = $this->explodeFQCN($pluginFQCN);

        if ($nameParts === false)
        {
            throw new ValidationException(
                    "Plugin FQCN '{$pluginFQCN}' doesn't follow the "
                  . "'Vendor\BundleName\VendorBundleName' convention.",
                    ValidationException::INVALID_FQCN);
        }

        $this->pluginFQCNParts = $nameParts;
    }

    private function checkDirectoryStructure($pluginFQCN)
    {
        $nameParts = $this->getPluginFQCNParts($pluginFQCN);

        $expectedVendorDir = $this->pluginDirectory
                . DIRECTORY_SEPARATOR
                . $nameParts['vendor'];
        $expectedPluginBundleDir = $expectedVendorDir
                . DIRECTORY_SEPARATOR
                . $nameParts['bundle'];

        if (! is_dir($expectedVendorDir))
        {
            throw new ValidationException(
                    "No vendor directory matches FQCN '{$pluginFQCN}' "
                  . "(expected directory : {$expectedVendorDir}).",
                    ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }

        if (! is_dir($expectedPluginBundleDir))
        {
            throw new ValidationException(
                    "No bundle directory matches FQCN '{$pluginFQCN}' "
                  . "(expected directory : {$expectedPluginBundleDir}).",
                    ValidationException::INVALID_DIRECTORY_STRUCTURE);
        }
    }

    private function checkIsLoadable($pluginFQCN)
    {
        $nameParts = $this->getPluginFQCNParts($pluginFQCN);

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
                    "No plugin class file matches FQCN '{$pluginFQCN}' "
                  . "(expected class file : {$expectedClassFile})",
                    ValidationException::INVALID_PLUGIN_CLASS_FILE);
        }

        require_once $expectedClassFile;

        if (! class_exists($pluginFQCN))
        {
            throw new ValidationException(
                    "Class '{$pluginFQCN}' not found in '{$expectedClassFile}'.",
                    ValidationException::INVALID_PLUGIN_CLASS);
        }

        $this->pluginInstance = new $pluginFQCN;
    }

    private function checkExtendsClarolinePlugin($pluginFQCN)
    {
        $pluginInstance = $this->getPluginInstance($pluginFQCN);
        $claroPluginClass = 'Claroline\PluginBundle\AbstractType\ClarolinePlugin';
        
        if (! is_a($pluginInstance, $claroPluginClass))
        {
            throw new ValidationException(
                    "Class '{$pluginFQCN}' doesn't extend '{$claroPluginClass}'.",
                    ValidationException::INVALID_PLUGIN_TYPE);
        }
    }
 
    private function checkRoutingResources($pluginFQCN)
    {
        $plugin = $this->getPluginInstance($pluginFQCN);
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
                        "{$pluginFQCN} : Cannot find routing file '{$path}'.",
                        ValidationException::INVALID_ROUTING_PATH);
            }

            $requiredLocation = realpath($plugin->getPath());

            // Checks that the provided resource path starts with the bundle one
            // (i.e. that the resource file is located within the plugin directory)
            if (substr($path, 0, strlen($requiredLocation)) != $requiredLocation)
            {                
                throw new ValidationException(
                        "{$pluginFQCN} : Invalid routing file '{$path}' "
                      . "(must be located within the bundle).",
                        ValidationException::INVALID_ROUTING_LOCATION);
            }
            
            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException(
                        "{$pluginFQCN} : Unsupported '{$ext}' extension for "
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
                        "{$pluginFQCN} : Unloadable YAML routing file "
                      . "(parse exception message : '{$ex->getMessage()}')",
                        ValidationException::INVALID_YAML_RESOURCE);
            }
        }
    }

    private function checkTranslationKeys($pluginFQCN)
    {
        $plugin = $this->getPluginInstance($pluginFQCN);
        $keys = array();
        $keys['name'] = $plugin->getNameTranslationKey();
        $keys['description'] = $plugin->getDescriptionTranslationKey();

        foreach ($keys as $type => $key)
        {
            if (! is_string($key))
            {
                throw new ValidationException(
                        "{$pluginFQCN} : {$type} translation key must be a string.",
                        ValidationException::INVALID_TRANSLATION_KEY);
            }

            if (empty($key))
            {
                throw new ValidationException(
                        "{$pluginFQCN} : {$type} translation key cannot be empty.",
                        ValidationException::INVALID_TRANSLATION_KEY);
            }
        }
    }

    // TODO : move this into a dedicated application validator
    private function checkApplicationConstraints($pluginFQCN)
    {
        $application = $this->getPluginInstance($pluginFQCN);

        if (! is_a($application, 'Claroline\PluginBundle\AbstractType\ClarolineApplication'))
        {
            return;
        }

        $launchers = $application->getLaunchers();

        // TODO : is it really necessary that getLaunchers() returns an array ? (No)
        if (! is_array($launchers))
        {
            throw new ValidationException(
                    "Method 'getLaunchers' from Application '{$pluginFQCN}' "
                  . "must return an array.",
                    ValidationException::INVALID_APPLICATION_LAUNCHER);
        }

        if (count($launchers) == 0)
        {
            throw new ValidationException(
                    "Application '{$pluginFQCN}' must define at least one launcher.",
                    ValidationException::INVALID_APPLICATION_LAUNCHER);
        }

        foreach ($launchers as $launcher)
        {
            if (! is_a($launcher, 'Claroline\GUIBundle\Widget\ApplicationLauncher'))
            {
                throw new ValidationException(
                        "Application '{$pluginFQCN}' has an invalid launcher.",
                        ValidationException::INVALID_APPLICATION_LAUNCHER);
            }
        }
    }
}