<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Validator\Constraints\CsvUser;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Claroline\CoreBundle\Listener\DoctrineDebug;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateUserFromCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:users:load')
            ->setDescription('Create users from a csv file');
        $this->setDefinition(
            array(new InputArgument('csv_user_path', InputArgument::REQUIRED, 'The absolute path to the csv file.'))
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array('csv_user_path' => 'Absolute path to the csv file: ');

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            $argumentName,
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
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
        $validator = $this->getContainer()->get('validator');
        $file = $input->getArgument('csv_user_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);
        $errors = $validator->validateValue($file, new CsvUser());

        if (count($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error> {$error->getMessage()} </error>");
            }
            throw new \Exception('The csv file is incorrect');
        }

        foreach ($lines as $line) {
            $users[] = str_getcsv($line, ';');
        }

        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $userManager->importUsers($users, false, function ($message) use ($output) {
            $output->writeln($message);
        });
    }
}
