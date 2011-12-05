<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UninstallCommand extends SinglePluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugin:uninstall')
             ->setDescription('Uninstalls a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendor = $input->getArgument('vendor_name');
        $bundle = $input->getArgument('bundle_name');
        $fqcn = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";        
        $installer = $this->getContainer()->get('claroline.plugin.installer');
        
        if ($installer->isInstalled($fqcn))
        {
            $output->writeln("Uninstalling plugin '{$fqcn}'...");
            $installer->uninstall($fqcn);
            $output->writeln('Done');
        }
        else
        {
            $output->writeln("Plugin '{$fqcn}' is not installed. Aborting.");
        }
        
        $this->resetCache($output);
    }
}