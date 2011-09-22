<?php
namespace Claroline\PluginBundle\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;

abstract class PluginMigration extends AbstractMigration
{
    protected function prefix()
    {
        $klass = new \ReflectionClass((get_called_class()));
        $namespace = $klass->getNamespaceName();
        $namespaceParts = explode("\\", $namespace);
        
        array_pop($namespaceParts); // remove the Migrations part
        $bundle = array_pop($namespaceParts);
        $vendor = array_pop($namespaceParts);
        
        $pluginClass = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
        
        $plugin = new $pluginClass();
        return $plugin->getPrefix();
        
    }
}