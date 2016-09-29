<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Import;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Claroline\CoreBundle\Listener\DoctrineDebug;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateUserFromCsvCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;
    private $params = ['csv_user_path' => 'Absolute path to the csv file: '];

    protected function configure()
    {
        $this->setName('claroline:users:load')
            ->setDescription('Create users from a csv file')
            ->setAliases(['claroline:csv:user']);
        $this->setDefinition(
            [new InputArgument('csv_user_path', InputArgument::REQUIRED, 'The absolute path to the csv file.')]
        );
        $this->addOption(
            'ignore-update',
            'i',
            InputOption::VALUE_NONE,
            'When set to true, updates are not triggered'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //validate the csv file...
        $consoleLogger = ConsoleLogger::get($output);
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $om->setLogger($consoleLogger)->activateLog();
        $this->getContainer()->get('claroline.doctrine.debug')->setLogger($consoleLogger)
            ->activateLog()
            ->setDebugLevel(DoctrineDebug::DEBUG_ALL)
            ->setVendor('Claroline');
        $file = $input->getArgument('csv_user_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);

        foreach ($lines as $line) {
            $users[] = str_getcsv($line, ';');
        }

        $options['ignore-update'] = $input->getOption('ignore-update');

        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $userManager->importUsers(
            $users,
            false,
            function ($message) use ($output) {
                $output->writeln($message);
            },
            [],
            false,
            $options
        );
    }
}
