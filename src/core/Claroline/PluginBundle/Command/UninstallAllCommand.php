<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class UninstallAllCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this->setName('claroline:plugin:uninstall_all')
             ->setDescription('Uninstalls all the plugins within "src/plugin".');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginInstaller = $this->getContainer()->get('claroline.plugin.installer');
        $pluginDirs = array(
            $this->getContainer()->getParameter('claroline.plugin.extension_directory'),
            $this->getContainer()->getParameter('claroline.plugin.application_directory'),
            $this->getContainer()->getParameter('claroline.plugin.tool_directory')
        );
        
        foreach ($pluginDirs as $pluginDir)
        {
            $output->writeln("Scanning plugin directory ('{$pluginDir}')...");

            $pluginVendors = new \DirectoryIterator($pluginDir);

            foreach ($pluginVendors as $vendor)
            {
                if ($vendor->isDir() && !$vendor->isDot())
                {
                    $vendorName = $vendor->getBasename();
                    $vendorPlugins = new \DirectoryIterator($vendor->getPathname());

                    foreach ($vendorPlugins as $plugin)
                    {
                        if ($plugin->isDir() && !$plugin->isDot())
                        {
                            $bundleName = $plugin->getBasename();
                            $fqcn = "{$vendorName}\\{$bundleName}\\{$vendorName}{$bundleName}";
                            
                            if ($pluginInstaller->isInstalled($fqcn))
                            {
                                $output->writeln("Uninstalling plugin '{$fqcn}'...");
                                $pluginInstaller->uninstall($fqcn);
                            }
                            else
                            {
                                $output->writeln("Plugin '{$fqcn}' is not installed. Aborting.");
                            }
                        }
                    }
                }
            }
        }

        $output->writeln('Done');
        
        $this->resetCache($output);
    }
}