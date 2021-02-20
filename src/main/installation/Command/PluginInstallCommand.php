<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Command;

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

        $this->setDescription('Installs a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $plugin = $this->getPlugin($input);
        $this->pluginInstaller->install($plugin);
        $this->resetCache($output);

        return 0;
    }
}
