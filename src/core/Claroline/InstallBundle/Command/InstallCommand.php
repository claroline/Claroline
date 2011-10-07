<?php

namespace Claroline\InstallBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:install')
             ->setDescription('Installs the platform according to the config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launching installer...');

        $manager = $this->getContainer()->get('claroline.install.core_installer');
        $manager->install();
        
        $command = $this->getApplication()->find('init:acl');        
        $command->run($input, $output);
        
        $output->writeln('Done');
        
    }
    
    
}