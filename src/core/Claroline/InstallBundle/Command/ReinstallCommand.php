<?php

namespace Claroline\InstallBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ReinstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:reinstall')
             ->setDescription('reinstall the platform according to config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
                
        $command = $this->getApplication()->find('claroline:uninstall');        
        $command->run($input, $output);

        $command = $this->getApplication()->find('claroline:install');        
        $command->run($input, $output);
        
    }
    
    
}