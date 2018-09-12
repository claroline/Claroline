<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\User;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Entity\User as UserEntity;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates an user, optionally with a specific role (default to simple user).
 */
class CreateCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'user_first_name' => 'first name',
        'user_last_name' => 'last name',
        'user_username' => 'username',
        'user_password' => 'password',
        'user_email' => 'email',
    ];

    protected function configure()
    {
        $this->setName('claroline:user:create')
            ->setDescription('Creates a new user.');
        $this->configureParams();
        $this->addOption(
            'ws_creator',
            'w',
            InputOption::VALUE_NONE,
            'When set to true, created user will have the workspace creator role'
        );
        $this->addOption(
            'admin',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, created user will have the admin role'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new UserEntity();
        $user->setFirstName($input->getArgument('user_first_name'));
        $user->setLastName($input->getArgument('user_last_name'));
        $user->setUsername($input->getArgument('user_username'));
        $user->setPlainPassword($input->getArgument('user_password'));
        $email = $input->getArgument('user_email');
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ?
            $email : $email.'@debug.net';
        $user->setEmail($email);

        if ($input->getOption('admin')) {
            $roleName = PlatformRoles::ADMIN;
        } elseif ($input->getOption('ws_creator')) {
            $roleName = PlatformRoles::WS_CREATOR;
        } else {
            $roleName = PlatformRoles::USER;
        }

        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $consoleLogger = ConsoleLogger::get($output);
        $userManager->setLogger($consoleLogger);
        $userManager->createUser($user, [], [$roleName]);
    }
}
