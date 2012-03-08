<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * Loads five users with the following roles :
     * 
     * Jane Doe  : ROLE_USER
     * Bob Doe   : ROLE_USER
     * Bill Doe  : ROLE_USER
     * Henry Doe : ROLE_WS_CREATOR (i.e. ROLE_USER -> ROLE_WS_CREATOR)
     * John Doe  : ROLE_ADMIN (i.e. ROLE_USER -> ROLE_WS_CREATOR -> ROLE_ADMIN)
     */
    public function load(ObjectManager $manager)
    {
        $userRole = $this->getReference('role/user');
        $wsCreatorRole = $this->getReference('role/ws_creator');
        $adminRole = $this->getReference('role/admin');
        
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUserName('user');
        $user->setPlainPassword('123');
        $user->addRole($userRole);
        
        $secondUser = new User();
        $secondUser->setFirstName('Bob');
        $secondUser->setLastName('Doe');
        $secondUser->setUserName('user_2');
        $secondUser->setPlainPassword('123');
        $secondUser->addRole($userRole);

        $thirdUser = new User();
        $thirdUser->setFirstName('Bill');
        $thirdUser->setLastName('Doe');
        $thirdUser->setUserName('user_3');
        $thirdUser->setPlainPassword('123');
        $thirdUser->addRole($userRole);
        
        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($wsCreatorRole);
        
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($adminRole);
        
        $manager->persist($user);
        $manager->persist($secondUser);
        $manager->persist($thirdUser);
        $manager->persist($wsCreator);
        $manager->persist($admin);
        /*     
        for($i=0; $i<100; $i++)
        {
            $this->createUser($i, $userRole, $manager);
        }
        
        for($i; $i<120; $i++)
        {
            $this->createUser($i, $wsCreatorRole, $manager);
        }
        
        for($i; $i<125; $i++)
        {
            $this->createUser($i, $adminRole, $manager);
        }   
        */
        $manager->flush();

        $this->addReference('user/user', $user);
        $this->addReference('user/user_2', $secondUser);
        $this->addReference('user/user_3', $thirdUser);
        $this->addReference('user/ws_creator', $wsCreator);
        $this->addReference('user/admin', $admin);
    }
    
    protected function createUser($number, $role, $manager)
    {
        $user = new User();
        $user->setFirstName("firstName{$number}");
        $user->setLastName("lastName{$number}");
        $user->setUserName("userName{$number}");
        $user->setPlainPassword("password{$number}");
        $user->addRole($role);
        $manager->persist($user);
    }

    
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}