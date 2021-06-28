<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Logs;

use Claroline\CoreBundle\Manager\LogManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogsFetcherCommand extends Command
{
    private $logManager;

    public function __construct(LogManager $logManager)
    {
        $this->logManager = $logManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Export logs');
        $this->setDefinition(
            [
                //1472688000 1st sept 2016
                new InputArgument('from', InputArgument::REQUIRED, 'date from (Y-m-d)'),
                new InputArgument('filePath', InputArgument::REQUIRED, 'path to exported file'),
                new InputArgument('doer', InputArgument::OPTIONAL, 'the uuid of the user.'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* @var LogManager $logManager */
        $this->logManager->exportLogsToCsv([
            'filters' => [
                'dateLog' => $input->getArgument('from') ?? null,
                'doer' => $input->getArgument('doer') ?? null,
            ],
        ], $input->getArgument('filePath'));

        $output->writeln('Check your file at '.$input->getArgument('filePath'));

        return 0;
    }
}
