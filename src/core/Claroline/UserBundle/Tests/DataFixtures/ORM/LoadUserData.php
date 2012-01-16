<?php

namespace Claroline\UserBundle\Tests\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\SecurityBundle\Manager\RoleManager;
use Claroline\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load($manager)
    {
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        
        $roleManager = $this->container->get('claroline.security.role_manager');
        $adminRole = $roleManager->getRole('ROLE_ADMIN', RoleManager::CREATE_IF_NOT_EXISTS);
        $admin->addRole($adminRole);
        
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUserName('user');
        $user->setPlainPassword('123');

        $userManager = $this->container->get('claroline.user.manager');    
        $userManager->create($admin);
        $userManager->create($user);

        $this->addReference('user/admin', $admin);
        $this->addReference('user/user', $user);
        
        return array(
            'admin' => $admin,
            'user' => $user
        );
    }
}