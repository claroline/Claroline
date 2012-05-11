<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Repository;

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
        $repository1 = new Repository();
        $user->setRepository($repository1);
        
        $secondUser = new User();
        $secondUser->setFirstName('Bob');
        $secondUser->setLastName('Doe');
        $secondUser->setUserName('user_2');
        $secondUser->setPlainPassword('123');
        $secondUser->addRole($userRole);
        $repository2 = new Repository();
        $secondUser->setRepository($repository2);

        $thirdUser = new User();
        $thirdUser->setFirstName('Bill');
        $thirdUser->setLastName('Doe');
        $thirdUser->setUserName('user_3');
        $thirdUser->setPlainPassword('123');
        $thirdUser->addRole($userRole);
        $repository3 = new Repository();
        $thirdUser->setRepository($repository3);
        
        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($wsCreatorRole);
        $repository4 = new Repository();
        $wsCreator->setRepository($repository4);
        
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($adminRole);
        $repository5 = new Repository();
        $admin->setRepository($repository5);
        
        $manager->persist($user);
        $manager->persist($secondUser);
        $manager->persist($thirdUser);
        $manager->persist($wsCreator);
        $manager->persist($admin);
        $manager->persist($repository1);
        $manager->persist($repository2);
        $manager->persist($repository3);
        $manager->persist($repository4);
        $manager->persist($repository5);
       
        $manager->flush();

        $this->addReference('user/user', $user);
        $this->addReference('user/user_2', $secondUser);
        $this->addReference('user/user_3', $thirdUser);
        $this->addReference('user/ws_creator', $wsCreator);
        $this->addReference('user/admin', $admin);
    }
        
    public function getOrder()
    {
        return 2;
    }
}