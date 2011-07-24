<?php

namespace Claroline\PluginBundle\Tests\Service\PluginManager;

use \vfsStream;

class VirtualPluginFactory
{
    private $pluginDirectoryName;

    public function __construct($pluginDirectoryName)
    {
        $this->pluginDirectoryName = $pluginDirectoryName;
        vfsStream::setup($pluginDirectoryName);
    }

    public function getPluginDirectoryName()
    {
        return $this->pluginDirectoryName;
    }

    public function getPluginDirectoryPath()
    {
        return vfsStream::url($this->pluginDirectoryName);
    }

    public function buildCompleteValidPlugin($vendorName, $pluginName)
    {
        $namespace = "{$vendorName}\\{$pluginName}";
        $className = "{$vendorName}{$pluginName}";
        $extends = '\Claroline\PluginBundle\AbstractType\ClarolinePlugin';
        $bundleClass = $this->pluginBundleClass($namespace, $className, $extends);
        $structure = $this->requiredStructure($vendorName, $pluginName, $bundleClass, true);

        vfsStream::create($structure, $this->pluginDirectoryName);
    }

    public function buildUnloadablePlugin($vendorName, $pluginName)
    {
        $namespace = "{$vendorName}\\{$pluginName}";
        $className = "BadPluginClassName";
        $extends = '\Claroline\PluginBundle\AbstractType\ClarolinePlugin';
        $bundleClass = $this->pluginBundleClass($namespace, $className, $extends);
        $structure = $this->requiredStructure($vendorName, $pluginName, $bundleClass);

        vfsStream::create($structure, $this->pluginDirectoryName);
    }

    public function buildNotClarolinePlugin($vendorName, $pluginName)
    {
        $namespace = "{$vendorName}\\{$pluginName}";
        $className = "{$vendorName}{$pluginName}";
        $extends = '\Symfony\Component\HttpKernel\Bundle\Bundle';
        $bundleClass = $this->pluginBundleClass($namespace, $className, $extends);
        $structure = $this->requiredStructure($vendorName, $pluginName, $bundleClass);

        vfsStream::create($structure, $this->pluginDirectoryName);
    }

    public function buildInconsistentRoutingResourcesPlugin($vendorName, $pluginName)
    {
        $namespace = "{$vendorName}\\{$pluginName}";
        $className = "{$vendorName}{$pluginName}";
        $extends = '\Claroline\PluginBundle\AbstractType\ClarolinePlugin';
        $content = 'public function getRoutingResourcesPaths()'
                 . '{return "wrong/path/file.foo";}';
        $bundleClass = $this->pluginBundleClass($namespace, $className, $extends, $content);
        $structure = $this->requiredStructure($vendorName, $pluginName, $bundleClass);

        vfsStream::create($structure, $this->pluginDirectoryName);
    }

    private function requiredStructure($vendorName, $bundleName, $pluginBundleClass, $routingFile = false)
    {
        $structure = array(
            $vendorName => array(
                $bundleName => array(
                    $vendorName . $bundleName . '.php' => $pluginBundleClass
                )
            )
        );

        if (true === $routingFile)
        {
            $structure[$vendorName][$bundleName]['Resources']['config']['routing.yml'] = '';
        }

        return $structure;
    }

    private function pluginBundleClass($namespace, $className, $extendedClass, $classContent = '')
    {
        $class = "<?php namespace {$namespace};\n"
               . "class {$className} extends {$extendedClass}\n"
               . "{\n{$classContent}\n}";

        return $class;
    }
}