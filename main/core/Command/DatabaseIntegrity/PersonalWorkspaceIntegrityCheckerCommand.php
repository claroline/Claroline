<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/22/17
 * Time: 1:24 PM.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PersonalWorkspaceIntegrityCheckerCommand extends Command
{
    private $userManager;
    private $conn;

    public function __construct(UserManager $userManager, Connection $conn)
    {
        $this->userManager = $userManager;
        $this->conn = $conn;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks the personal workspace integrity of the platform.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User login or email. Checks integrity only for this user.')
            ->addOption('personal', 'p', InputOption::VALUE_NONE, 'Only check the is_personal parameter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = ConsoleLogger::get($output);
        $userManager = $this->userManager;
        $userManager->setLogger($consoleLogger);

        if ($input->getOption('personal')) {
            $consoleLogger->warning('Restoring is_personal parameter');
            $sql = '
                UPDATE claro_workspace workspace
                JOIN claro_user user on workspace.id = user.workspace_id
                SET workspace.is_personal = true
            ';

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return 0;
        }

        $userId = $input->getOption('user');
        if (!empty($userId)) {
            $user = $userManager->getUserByUsernameOrMail($userId, $userId);
            if (empty($user)) {
                $consoleLogger->warning("Could not find user \"{$userId}\"");

                return 1;
            }
            $userManager->checkPersonalWorkspaceIntegrityForUser($user);

            return 0;
        }
        $userManager->checkPersonalWorkspaceIntegrity();

        return 0;
    }
}
