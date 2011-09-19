<?php
namespace Claroline\PluginBundle\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;

abstract class PluginMigration extends AbstractMigration
{
    protected function prefix()
    {
        $klass = new \ReflectionClass((get_called_class()));
        $namespace = $klass->getNamespaceName();
        $namespace_parts = explode("\\", $namespace);
        
        array_pop($namespace_parts); // remove the Migrations part
        $bundle = array_pop($namespace_parts);
        $vendor = array_pop($namespace_parts);
        
        $plugin_klass = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
        
        $plugin = new $plugin_klass();
        return $plugin->getPrefix();
        
    }
}