<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/22/17
 * Time: 1:24 PM.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PersonalWorkspaceIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:personal_ws:check')
            ->setDescription('Checks the personal workspace integrity of the platform.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User login or email. Checks integrity only for this user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $userManager->setLogger($consoleLogger);
        $userId = $input->getOption('user');
        if (!empty($userId)) {
            $user = $userManager->getUserByUsernameOrMail($userId, $userId);
            if (empty($user)) {
                $consoleLogger->warning("Could not find user \"{$userId}\"");

                return;
            }
            $userManager->checkPersonalWorkspaceIntegrityForUser($user);

            return;
        }
        $userManager->checkPersonalWorkspaceIntegrity();
    }
}
