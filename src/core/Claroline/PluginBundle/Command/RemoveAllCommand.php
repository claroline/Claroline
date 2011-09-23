<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RemoveAllCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this->setName('claroline:plugin:remove_all')
             ->setDescription('UnRegisters all the plugins within "src/plugin".');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginManager = $this->getContainer()->get('claroline.plugin.manager');
        $pluginPath = $this->getContainer()->getParameter('claroline.plugin.directory');
        
        $output->writeln("Scanning plugin directory ('{$pluginPath}')...");

        $pluginVendors = new \DirectoryIterator($pluginPath);

        foreach ($pluginVendors as $vendor)
        {
            if ($vendor->isDir() && ! $vendor->isDot())
            {
                $vendorName = $vendor->getBasename();
                $vendorPlugins = new \DirectoryIterator($vendor->getPathname());

                foreach ($vendorPlugins as $plugin)
                {
                    if ($plugin->isDir() && ! $plugin->isDot())
                    {
                        $bundleName = $plugin->getBasename();
                        $fqcn = "{$vendorName}\\{$bundleName}\\{$vendorName}{$bundleName}";
                        if(!$pluginManager->isInstalled($fqcn))
                        {
                            continue;
                        }
                        $output->writeln("Uninstalling plugin '{$fqcn}'...");
                        $pluginManager->remove($fqcn);
                    }
                }
            }
        }

        $output->writeln('Done');
        
        $this->resetCache($input, $output);
    }
}