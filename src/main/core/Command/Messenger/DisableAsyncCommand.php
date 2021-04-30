<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Messenger;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableAsyncCommand extends Command
{
    private $configurationHandler;

    public function __construct(PlatformConfigurationHandler $configurationHandler, string $name = null)
    {
        $this->configurationHandler = $configurationHandler;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Disable async');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configurationHandler->setParameter('job_queue.enabled', false);
        $output->writeln('Async has been disabled.');

        return 0;
    }
}
