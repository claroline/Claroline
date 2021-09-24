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

use Claroline\InstallationBundle\Manager\RefreshManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends Command
{
    private $refresher;

    public function __construct(RefreshManager $refresher)
    {
        $this->refresher = $refresher;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Installs/dumps the assets and empties the cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->refresher->setOutput($output);
        $this->refresher->refresh($input->getOption('env'));

        return 0;
    }
}
