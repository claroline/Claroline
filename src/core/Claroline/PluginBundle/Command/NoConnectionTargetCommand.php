<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoConnectionTargetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:application:no_connection_target')
             ->setDescription('Unsets the connection target flag of the current target application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launching application manager...');

        $manager = $this->getContainer()->get('claroline.plugin.application_manager');
        $manager->unsetCurrentConnectionTarget();
        
        $output->writeln('Done');
    }
}