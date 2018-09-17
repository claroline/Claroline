<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class ApiImportCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'file' => 'Absolute path to the file: ',
        'action' => 'The action to execute:',
        'owner' => 'The username doing the action',
    ];

    protected function configure()
    {
        $this->setName('claroline:app:import')->setDescription('import a file');

        $this->setDefinition(
            [
              new InputArgument('file', InputArgument::REQUIRED, 'The absolute path to the csv file.'),
              new InputArgument('action', InputArgument::REQUIRED, 'The action to execute.'),
              new InputArgument('owner', InputArgument::REQUIRED, 'The username doing the action.'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $action = $input->getArgument('action');
        $consoleLogger = ConsoleLogger::get($output);
        $this->getContainer()->get('claroline.authenticator')->authenticate($input->getArgument('owner'), null, false);
        $this->getContainer()->get('claroline.api.transfer')->setLogger($consoleLogger);

        $this->getContainer()->get('claroline.api.transfer')->execute(
          file_get_contents($file),
          $action,
          'text/csv'
        );
    }
}
