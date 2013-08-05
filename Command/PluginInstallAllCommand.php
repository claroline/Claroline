<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installs all the plugins found in the "plugin" directory.
 */
class PluginInstallAllCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this->setName('claroline:plugin:install_all')
            ->setDescription('Registers all the plugins within "src/plugin".');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->walkPluginDirectory('installPlugin', $output)) {
            $this->installAssets($output);
        }

        $output->writeln('Done');
    }
}
