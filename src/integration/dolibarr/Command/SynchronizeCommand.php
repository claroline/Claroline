<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DolibarrBundle\Command;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeCommand extends Command
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var Crud */
    private $crud;

    public function __construct(
        PlatformConfigurationHandler $config,
        Crud $crud
    ) {
        $this->config = $config;
        $this->crud = $crud;

        parent::__construct();
    }

    protected function configure()
    {
        // TODO : describe me
        $this->setDescription('DESCRIBE ME');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Synchronizing data with dolibarr platform...');

        // TODO : implement me

        return 0;
    }
}
