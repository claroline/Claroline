<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installs a specific plugin.
 */
class PluginInstallCommand extends SinglePluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugin:install')
            ->setDescription('Registers a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendor = $input->getArgument('vendor_name');
        $bundle = $input->getArgument('bundle_name');
        $fqcn = "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";

        if ($this->installPlugin($fqcn, $output)) {
            $this->resetCache($output);
            $this->installAssets($output);
        }
    }
}