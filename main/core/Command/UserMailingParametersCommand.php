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

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installs a plugin.
 */
class UserMailingParametersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:user:mailing');
        $this->setDefinition(
            [new InputArgument('user_username', InputArgument::OPTIONAL, 'The user username')]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('user_username');
        $consoleLogger = ConsoleLogger::get($output);
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $userManager->setLogger($consoleLogger);
        if ($username) {
            $user = $userManager->getUserByUsername($username);
            $userManager->restoreUserMailParameter($user);
        } else {
            $userManager->restoreUsersMailParameter();
        }
    }
}
