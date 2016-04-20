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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Constraints\CsvWorkspace;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateWorkspaceFromCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspaces:load')
            ->setDescription('Create workspaces from a csv file');
        $this->setDefinition(
            array(new InputArgument('csv_workspace_path', InputArgument::REQUIRED, 'The absolute path to the csv file.'))
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        //@todo ask authentication source
        $params = array('csv_workspace_path' => 'Absolute path to the workspace file: ');

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
        $validator = $this->getContainer()->get('validator');
        $file = $input->getArgument('csv_workspace_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);
        $errors = $validator->validateValue($file, new CsvWorkspace());

        if (count($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error> {$error->getMessage()} </error>");
            }
            throw new \Exception('The csv file is incorrect');
        }

        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $workspaces[] = str_getcsv($line, ';');
            }
        }

        $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
        $workspaceManager->importWorkspaces($workspaces, function ($message) use ($output) {
            $output->writeln($message);
        });
    }
}
