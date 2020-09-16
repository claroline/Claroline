<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RightsIntegrityCheckerCommand extends Command
{
    private $rightsManager;

    public function __construct(RightsManager $rightsManager)
    {
        $this->rightsManager = $rightsManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Checks the rights integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = ConsoleLogger::get($output);
        $this->rightsManager->setLogger($consoleLogger);
        $this->rightsManager->checkIntegrity();

        return 0;
    }
}
