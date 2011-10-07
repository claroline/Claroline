<?php

namespace Claroline\InstallBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class UninstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:uninstall')
             ->setDescription('uninstall the platform to a clear state.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launching uninstaller...');
        
        $command = $this->getApplication()->find('claroline:security:drop_acl');        
        $command->run($input, $output);

        $manager = $this->getContainer()->get('claroline.install.core_installer');
        $manager->uninstall();
        
        
        
        $output->writeln('Done');
        
    }
    
    
}