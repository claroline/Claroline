<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class Validator
{
    private $pluginDirectory;
    private $yamlParser;

    public function __construct($pluginDirectory, Parser $yamlParser)
    {
        $this->setPluginDirectory($pluginDirectory);
        $this->yamlParser = $yamlParser;
    }

    public function setPluginDirectory($pluginDirectory)
    {
        if (! is_dir($pluginDirectory))
        {
            throw new ValidationException("'{$pluginDirectory}' is not a valid directory.",
                                          ValidationException::INVALID_PLUGIN_DIR);
        }

        $this->pluginDirectory = $this->resolvePath($pluginDirectory);
    }

    public function check($pluginFQCN)
    {
        $this->checkPluginFQCNFollowsConventions($pluginFQCN);
        $this->checkPluginFQCNMatchesAnExistingPluginDirectoryStructure($pluginFQCN);
        $this->checkPluginBundleClassIsLoadable($pluginFQCN);
        $this->checkPluginBundleClassExtendsClarolinePlugin($pluginFQCN);
        $this->checkPluginClassReturnsValidRoutingValue($pluginFQCN);
        $this->checkPluginClassReturnsValidTranslationKeys($pluginFQCN);
        $this->checkApplicationConstraints($pluginFQCN);
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
            $path = $this->resolvePath($path);

            if (! file_exists($path))
            {
                throw new ValidationException("{$pluginFQCN} : Cannot find routing file '{$path}'.",
                                              ValidationException::INVALID_ROUTING_PATH);
            }

            $nameParts = explode('\\', $pluginFQCN);
            $vendor = $nameParts[0];
            $bundleName = $nameParts[1];
            $requiredLocation = 
                implode(    DIRECTORY_SEPARATOR, 
                            array($this->pluginDirectory,$vendor, $bundleName)
                        );
            
            
            if (substr($path, 0, strlen($requiredLocation)) != $requiredLocation)
            {                
                throw new ValidationException("{$pluginFQCN} : Invalid routing file '{$path}' "
                                            . "(must be located within the bundle).",
                                              ValidationException::INVALID_ROUTING_LOCATION);
            }

            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION))
            {
                throw new ValidationException("{$pluginFQCN} : Unsupported '{$ext}' extension for "
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
                throw new ValidationException("{$pluginFQCN} : Unloadable YAML routing file "
                                            . "(parse exception message : '{$ex->getMessage()}')",
                                              ValidationException::INVALID_YAML_RESOURCE);
            }
        }
    }

    private function checkPluginClassReturnsValidTranslationKeys($pluginFQCN)
    {
        $plugin = new $pluginFQCN;
        $keys = array();
        $keys[] = $plugin->getNameTranslationKey();
        $keys[] = $plugin->getDescriptionTranslationKey();

        foreach($keys as $key)
        {
            if (! is_string($key))
            {
                throw new ValidationException("{$pluginFQCN} : translation key must be a string.",
                                              ValidationException::INVALID_TRANSLATION_KEY);
            }

            if (empty($key))
            {
                throw new ValidationException("{$pluginFQCN} : translation key cannot be empty.",
                                              ValidationException::INVALID_TRANSLATION_KEY);
            }
        }
    }

    private function checkApplicationConstraints($pluginFQCN)
    {
        $application = new $pluginFQCN;

        if (! is_a($application, 'Claroline\PluginBundle\AbstractType\ClarolineApplication'))
        {
            return;
        }

        $launchers = $application->getLaunchers();

        if (! is_array($launchers))
        {
            throw new ValidationException("Method 'getLaunchers' from Application '{$pluginFQCN}' "
                                        . "must return an array.",
                                          ValidationException::INVALID_APPLICATION_LAUNCHER);
        }

        if (count($launchers) == 0)
        {
            throw new ValidationException("Application '{$pluginFQCN}' must define at least one launcher.",
                                          ValidationException::INVALID_APPLICATION_LAUNCHER);
        }

        foreach ($launchers as $launcher)
        {
            if (! is_a($launcher, 'Claroline\GUIBundle\Widget\ApplicationLauncher'))
            {
                throw new ValidationException("Application '{$pluginFQCN}' has an invalid launcher.",
                                              ValidationException::INVALID_APPLICATION_LAUNCHER);
            }
        }
    }

    // Workaround for https://github.com/mikey179/vfsStream/wiki/KnownIssues
    // Use of the realpath() function may be necessary for real file paths comparison,
    // but it'll always return false for "virtual" paths coming from vfsstream.
    private function resolvePath($path)
    {
        $resolvedPath  = realpath($path);

        if ($resolvedPath !== false)
        {
            return $resolvedPath;
        }

        return $path;
    }
}