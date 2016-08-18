<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Command;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs a fresh installation of the platform.
 */
class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:chat:import_users')
            ->setDescription('Import the users into prosody');
        $this->setDefinition(
            [
                new InputArgument('user_first_name', InputArgument::OPTIONAL, 'The user first name'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $chatManager = $this->getContainer()->get('claroline.manager.chat_manager');
        $chatManager->setLogger($consoleLogger);
        $username = $input->getArgument('user_first_name');
        $user = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User')->findOneByUsername($username);
        if ($user) {
            $chatManager->importUser($user);
        } else {
            $chatManager->importExistingUsers();
        }
    }
}
