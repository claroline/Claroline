<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:users:create')
            ->setDescription('Creates a lot of users.');
        $this->setDefinition(array(
            new InputArgument('amount', InputArgument::REQUIRED, 'The number of users created'),
        ));
        $this->addOption(
            'ws_creator', 'wsc', InputOption::VALUE_NONE, "When set to true, created users will have the workspace creator role"
        );
        $this->addOption(
            'admin', 'a', InputOption::VALUE_NONE, "When set to true, created users will have the admin role"
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'amount' => 'the number of users'
        );

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
            $output, "Enter the user {$argumentName}: ", function($argument) {
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
        $number = $input->getArgument('amount');

        for ($i=0; $i<$number; $i++) {
            $user = new User();
            $user->setFirstName($this->getContainer()->get('claroline.resource.utilities')->generateGuid());
            $user->setLastName($this->getContainer()->get('claroline.resource.utilities')->generateGuid());
            $user->setUsername($this->getContainer()->get('claroline.resource.utilities')->generateGuid());
            $user->setPlainPassword('123');
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $roleRepo = $em->getRepository('Claroline\CoreBundle\Entity\Role');

            if ($input->getOption('admin')) {
                $adminRole = $roleRepo->findOneByName(PlatformRoles::ADMIN);
                $user->addRole($adminRole);
            } elseif ($input->getOption('ws_creator')) {
                $wsCreatorRole = $roleRepo->findOneByName(PlatformRoles::WS_CREATOR);
                $user->addRole($wsCreatorRole);
            } else {
                $userRole = $roleRepo->findOneByName(PlatformRoles::USER);
                $user->addRole($userRole);
            }

            $em->persist($user);
            $config = new Configuration();
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $config->setWorkspaceName($user->getUsername());
            $config->setWorkspaceCode('PERSO');
            $wsCreator = $this->getContainer()->get('claroline.workspace.creator');
            $workspace = $wsCreator->createWorkspace($config, $user);
            $workspace->setType(AbstractWorkspace::USER_REPOSITORY);
            $user->addRole($workspace->getManagerRole());
            $user->setPersonnalWorkspace($workspace);
            $em->persist($workspace);
            $em->flush();
        }
    }
}