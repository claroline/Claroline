<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class contains common methods for the single plugin install/uninstall commands.
 */
abstract class SinglePluginCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputArgument('vendor_name', InputArgument::REQUIRED, 'The plugin vendor'),
                new InputArgument('bundle_name', InputArgument::REQUIRED, 'The plugin bundle name'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('vendor_name')) {
            $vendorName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the plugin vendor: ',
                function ($vendorName) {
                    if (empty($vendorName)) {
                        throw new \Exception('Vendor name cannot be empty');
                    }

                    return $vendorName;
                }
            );
            $input->setArgument('vendor_name', $vendorName);
        }

        if (!$input->getArgument('bundle_name')) {
            $bundleName = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Enter the plugin bundle name: ',
                function ($bundleName) {
                    if (empty($bundleName)) {
                        throw new \Exception('Bundle name cannot be empty');
                    }

                    return $bundleName;
                }
            );
            $input->setArgument('bundle_name', $bundleName);
        }
    }
}