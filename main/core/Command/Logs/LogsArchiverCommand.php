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

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LogsArchiverCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:logs:archive')
            ->setDescription('Archive logs');
        $this->addOption(
            'keep',
            'k',
            InputOption::VALUE_NONE,
            'When set to true, keep the archived logs (format: m-d-Y)'
        );
        $this->addOption(
            'from',
            'f',
            InputOption::VALUE_REQUIRED,
            'From date (format: m-d-Y)'
        );
        $this->addOption(
            'to',
            't',
            InputOption::VALUE_REQUIRED,
            'To date (format: m-d-Y)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getOption('from');
        $to = $input->getOption('to');

        if ($from) {
            $from = \DateTime::createFromFormat('m-d-Y', $from);
        } else {
            $from = $this->getContainer()->get('claroline.persistence.object_manager')->getRepository(Log::class)->findBy([], null, 1)[0]->getDateLog();
        }

        $searches = ['dateLog' => $from->format('Y-m-d h:i:s')];

        if ($to) {
            $to = \DateTime::createFromFormat('m-d-Y', $to);
            $searches['dateTo'] = $to->format('Y-m-d h:i:s');
        }

        $consoleLogger = ConsoleLogger::get($output);
        $databaseManager = $this->getContainer()->get('claroline.manager.database_manager');
        $databaseManager->setLogger($consoleLogger);

        $logTables = [
            'claro_log',
        ];

        $searches = [];

        $delete = $input->getOption('keep') ? false : true;

        foreach ($logTables as $table) {
            $name = str_replace('-', '_', $from->format('Y')).'_'.uniqid();

            $databaseManager->backupRows(
                Log::class,
                $searches,
                $table,
                $name,
                $delete
            );
        }
    }
}
