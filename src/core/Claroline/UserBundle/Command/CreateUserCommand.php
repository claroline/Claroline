<?php

namespace Claroline\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Claroline\UserBundle\Entity\User;
use Claroline\SecurityBundle\Service\RoleManager;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:user:create')
             ->setDescription('Creates a new user.');
        $this->setDefinition(array(
            new InputArgument('user_first_name', InputArgument::REQUIRED, 'The user first name'),
            new InputArgument('user_last_name', InputArgument::REQUIRED, 'The user last name'),
            new InputArgument('user_username', InputArgument::REQUIRED, 'The user username'),
            new InputArgument('user_password', InputArgument::REQUIRED, 'The user password')
        ));
        $this->addOption(
            'admin', 
            'a', 
            InputOption::VALUE_NONE, 
            "When set to true, created user will have the admin role"
        );
    }
    
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'user_first_name' => 'first name',
            'user_last_name' => 'last name',
            'user_username' => 'username',
            'user_password' => 'password'
        );
        
        foreach ($params as $argument => $argumentName)
        {
            if (!$input->getArgument($argument))
            {
                $input->setArgument(
                    $argument, 
                    $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            "Enter the user {$argumentName}: ",
            function($argument)
            {
                if (empty($argument))
                {
                    throw new \Exception('This argument is required');
                }
                return $argument;
            }
        );
        
        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $firstName = $input->getArgument('user_first_name');
        $lastName = $input->getArgument('user_last_name');
        $username = $input->getArgument('user_username');
        $password = $input->getArgument('user_password');
        
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setPlainPassword($password);
        
        if ($input->getOption('admin'))
        {            
            $roleManager = $this->getContainer()->get('claroline.security.role_manager');
            $adminRole = $roleManager->getRole('ROLE_ADMIN', RoleManager::CREATE_IF_NOT_EXISTS);
            $user->addRole($adminRole);
        }
        
        $manager = $this->getContainer()->get('claroline.user.manager');
        $manager->create($user);
    }
}