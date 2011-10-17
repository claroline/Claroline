<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConnectionTargetCommand extends SinglePluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:application:connection_target')
             ->setDescription('Marks a specified claroline application as connection target.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendor = $input->getArgument('vendor_name');
        $bundle = $input->getArgument('bundle_name');
        $fqcn = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";

        $output->writeln('Launching application manager...');

        $manager = $this->getContainer()->get('claroline.plugin.application_manager');
        $manager->markAsConnectionTarget($fqcn);
        
        $output->writeln('Done');
    }
}