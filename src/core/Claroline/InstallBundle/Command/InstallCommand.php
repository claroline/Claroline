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
        $manager = $this->getContainer()->get('claroline.install.core_installer');
        
        $output->writeln('Installing the platform...');
        $manager->install();
        
        $aclCommand = $this->getApplication()->find('init:acl');        
        $aclCommand->run(new ArrayInput(array('command' => 'init:acl')), $output);
        
        if ($input->getOption('with-plugins'))
        {            
            $pluginCommand = $this->getApplication()->find('claroline:plugin:install_all');
            $pluginCommand->run(new ArrayInput(array('command' => 'claroline:plugin:install_all')), $output);
        }
        
        $assetCommand = $this->getApplication()->find('assets:install');
        $input = new ArrayInput(array(
            'command' => 'assets:install',
            'target' => __DIR__ . '/../../../../../web',
            '--symlink'=> false
        ));
        $assetCommand->run($input, $output);        
        
        $output->writeln('Done');
    }
}