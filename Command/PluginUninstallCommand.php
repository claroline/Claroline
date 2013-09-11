<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Uninstalls a plugin.
 */
class PluginUninstallCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugin:uninstall')
            ->setDescription('Uninstalls a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $this->getPlugin($input);
        $this->getPluginInstaller($output)->uninstall($plugin);
        $this->resetCache($output);
    }
}
