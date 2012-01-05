<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Claroline\CommonBundle\Exception\ClarolineException;

abstract class AbstractPluginCommand extends ContainerAwareCommand
{
    protected function installPlugin($fqcn, OutputInterface $output)
    {
        $installer = $this->getContainer()->get('claroline.plugin.installer');
        
        if (! $installer->isInstalled($fqcn))
        {
            $output->writeln("Installing plugin '{$fqcn}'...");
            $installer->install($fqcn);
            $output->writeln('Done');
            
            return true;
        }
        
        $output->writeln("Plugin '{$fqcn}' is already installed.");
        
        return false;
    }
    
    protected function uninstallPlugin($fqcn, OutputInterface $output)
    {
        $installer = $this->getContainer()->get('claroline.plugin.installer');
        
        if ($installer->isInstalled($fqcn))
        {
            $output->writeln("Uninstalling plugin '{$fqcn}'...");
            $installer->uninstall($fqcn);
            $output->writeln('Done');
            
            return true;
        }
        
        $output->writeln("Plugin '{$fqcn}' is not installed.");
        
        return false;
    }
    
    /**
     * Helper method parsing the plugin directories and applying the "installPlugin"
     * or "uninstallPlugin" methods of this class on each plugin found.
     * 
     * @param string $methodName "installPlugin" or "uninstallPlugin"
     * @param OutputInterface $output
     * @return boolean True if the callback method has succeeded at least once, false otherwise
     */
    protected function walkPluginDirectories($methodName, OutputInterface $output)
    {
        if ($methodName != 'installPlugin' && $methodName != 'uninstallPlugin')
        {
            throw new ClarolineException(
                "First parameter must be either 'installPlugin' "
                . " or 'uninstallPlugin', {$methodName} given."
            );
        }     
   
        $pluginDirs = array(
            $this->getContainer()->getParameter('claroline.plugin.extension_directory'),
            $this->getContainer()->getParameter('claroline.plugin.tool_directory')
        );
        
        $hasEffect = false;
        
        foreach ($pluginDirs as $pluginDir)
        {
            $output->writeln("Scanning plugin directory ('{$pluginDir}')...");
            
            $pluginFQCNs = $this->getAvailablePluginFQCNs($pluginDir);
            
            foreach ($pluginFQCNs as $pluginFQCN)
            {
                if ($this->{$methodName}($pluginFQCN, $output))
                {
                    $hasEffect = true;
                }
            }
        }
        
        return $hasEffect;
    }
    
    protected function resetCache(OutputInterface $output)
    {
        $command = $this->getApplication()->find('cache:clear');
        
        $input = new ArrayInput(array(
            'command' => 'cache:clear', // strange but doesn't work if removed
            '--no-warmup' => true,
        ));
        
        $command->run($input, $output);
    }
    
    protected function installAssets(OutputInterface $output)
    {
        $command = $this->getApplication()->find('assets:install');
        $input = new ArrayInput(array(
            'command' => 'assets:install',
            'target' => realpath(__DIR__ . '/../../../../../web'),
            '--symlink'=> false
        ));
        $command->run($input, $output);
    }
    
    private function getAvailablePluginFQCNs($pluginDirectory)
    {
        $fqcns = array();
        $pluginVendors = new \DirectoryIterator($pluginDirectory);

        foreach ($pluginVendors as $vendor)
        {
            if (!$vendor->isDir() || $vendor->isDot())
            {
                continue;
            }
            $vendorName = $vendor->getBasename();
            $vendorPlugins = new \DirectoryIterator($vendor->getPathname());

            foreach ($vendorPlugins as $plugin)
            {
                if (!$plugin->isDir() ||$plugin->isDot())
                {
                    continue;
                }
                $bundleName = $plugin->getBasename();
                $fqcns[] = "{$vendorName}\\{$bundleName}\\{$vendorName}{$bundleName}";
            }
        }
        
        return $fqcns;
    }
}