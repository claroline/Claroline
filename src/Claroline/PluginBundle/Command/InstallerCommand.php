<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class InstallerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:plugin:install')
             ->setDescription('Registers a specified claroline plugin.')
             ->setDefinition(array(
                 new InputArgument('vendor_name', InputArgument::REQUIRED, 'The plugin\'s vendor'),
                 new InputArgument('bundle_name', InputArgument::REQUIRED, 'The plugin\'s bundle name'),
                 ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendor = $input->getArgument('vendor_name');
        $bundle = $input->getArgument('bundle_name');
        $FQCN = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";

        $output->writeln('Launching installer...');

        $manager = $this->getContainer()->get('claroline.plugin.manager');
        $manager->install($FQCN);

        $output->writeln('Done');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('vendor_name'))
        {
            $vendorName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the plugin\'s vendor: ',
                function($vendorName)
                {
                    if (empty($vendorName))
                    {
                        throw new \Exception('Vendor name cannot be empty');
                    }
                    return $vendorName;
                }
            );
            $input->setArgument('vendor_name', $vendorName);
        }

        if (!$input->getArgument('bundle_name'))
        {
            $bundleName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the plugin\'s bundle name: ',
                function($bundleName)
                {
                    if (empty($bundleName))
                    {
                        throw new \Exception('Bundle name cannot be empty');
                    }
                    return $bundleName;
                }
            );
            $input->setArgument('bundle_name', $bundleName);
        }
    }
}