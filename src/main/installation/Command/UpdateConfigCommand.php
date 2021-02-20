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

use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateConfigCommand extends Command
{
    private $pluginInstaller;

    public function __construct(Installer $pluginInstaller)
    {
        $this->pluginInstaller = $pluginInstaller;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Runs the local update the config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pluginInstaller->updateAllConfigurations();

        return 0;
    }
}
