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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MessengerTransportCommand extends Command
{
    private $configurationHandler;
    private const TRANSPORT_ALLOWED = [
        'doctrine',
        'redis'
    ];

    public function __construct(
        PlatformConfigurationHandler $configurationHandler,
        string $name = null
    ) {
        $this->configurationHandler = $configurationHandler;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Set messenger transport for async')
            ->addArgument(
                'transport',
                InputOption::VALUE_REQUIRED,
                'Which transport do you want to use ?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!in_array($input->getArgument('transport'), self::TRANSPORT_ALLOWED)) {
            $output->writeln('Invalid or missing argument');
            $output->writeln('Valid arguments are : ' . implode(', ', self::TRANSPORT_ALLOWED));
            return 0;
        }

        $this->configurationHandler->setParameter('job_queue.transport',  $input->getArgument('transport'));
        $output->writeln( "{$input->getArgument('transport')} has been defined as transport.");
        return 0;

    }
}
