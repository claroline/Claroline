<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoIndexApplicationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:application:no_index')
             ->setDescription('Unsets the index flag of the current index application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launching application manager...');

        $manager = $this->getContainer()->get('claroline.plugin.application_manager');
        $manager->unsetCurrentIndexApplication();
        
        $output->writeln('Done');
    }
}