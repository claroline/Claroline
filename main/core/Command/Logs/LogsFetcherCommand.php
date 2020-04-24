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

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Manager\LogManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogsFetcherCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'from' => 'from',
        'filePath' => 'filePath',
    ];

    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:logs:fetch')
            ->setDescription('Export logs');
        $this->setDefinition(
            [
                //1472688000 1st sept 2016
                new InputArgument('from', InputArgument::REQUIRED, 'date from (Y-m-d)'),
                new InputArgument('filePath', InputArgument::REQUIRED, 'path to exported file'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var LogManager $logManager */
        $logManager = $this->getContainer()->get('claroline.log.manager');
        $logManager->exportLogsToCsv([
            'filters' => [
                'dateLog' => $input->getArgument('from') ?? null,
            ],
        ], $input->getArgument('filePath'));

        $output->writeln('Check your file at '.$input->getArgument('filePath'));
    }
}
