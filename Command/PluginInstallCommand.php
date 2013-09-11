<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installs a plugin.
 */
class PluginInstallCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugin:install')
            ->setDescription('Installs a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $this->getPlugin($input, false);
        $this->getPluginInstaller($output)->install($plugin);
        $this->resetCache($output);
    }
}
