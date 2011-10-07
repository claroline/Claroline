<?php

namespace Claroline\InstallBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:install')
             ->setDescription('Installs the platform according to the config.');
    
        $this->addOption(
            'with-plugins', 
            'w', 
            InputOption::VALUE_NONE, 
            "When set to true, available plugins will be installed"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Launching installer...');
        $delegateInput = new ArrayInput($input->getArguments());

        $manager = $this->getContainer()->get('claroline.install.core_installer');
        $manager->install();
        
        $command = $this->getApplication()->find('init:acl');        
        $command->run($delegateInput, $output);
        
        if($input->getOption('with-plugins'))
        {            
            $pluginInstaller = $this->getApplication()->find('claroline:plugin:install_all');
            $pluginInstaller->run($delegateInput, $output);
        }
        
        $output->writeln('Done');
        
    }
    
    
}