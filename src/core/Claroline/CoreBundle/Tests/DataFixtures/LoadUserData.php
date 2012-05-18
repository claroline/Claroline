<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;

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
        
        $wsCreatorService = $this->container->get('claroline.workspace.creator');
        $type = Configuration::TYPE_SIMPLE;
        $config = new Configuration();
        $config->setWorkspaceType($type);
        $config->setWorkspaceName("my workspace");
        
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUserName('user');
        $user->setPlainPassword('123');
        $user->addRole($userRole);
        $manager->persist($user);
        $repositoryOne = $wsCreatorService->createWorkspace($config, $user);
        $repositoryOne->setType('user_repository');
        $user->addRole($repositoryOne->getManagerRole());
        
        $secondUser = new User();
        $secondUser->setFirstName('Bob');
        $secondUser->setLastName('Doe');
        $secondUser->setUserName('user_2');
        $secondUser->setPlainPassword('123');
        $secondUser->addRole($userRole);
        $manager->persist($secondUser);
        $repositoryTwo = $wsCreatorService->createWorkspace($config, $secondUser);
        $repositoryTwo->setType('user_repository');
        $secondUser->addRole($repositoryTwo->getManagerRole());

        $thirdUser = new User();
        $thirdUser->setFirstName('Bill');
        $thirdUser->setLastName('Doe');
        $thirdUser->setUserName('user_3');
        $thirdUser->setPlainPassword('123');
        $thirdUser->addRole($userRole);
        $manager->persist($thirdUser);
        $repositoryThree = $wsCreatorService->createWorkspace($config, $thirdUser);
        $repositoryThree->setType('user_repository');
        $thirdUser->addRole($repositoryThree->getManagerRole());
        
        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($wsCreatorRole);
        $manager->persist($wsCreator);
        $repositoryFour = $wsCreatorService->createWorkspace($config, $wsCreator);
        $repositoryFour->setType('user_repository');
        $wsCreator->addRole($repositoryFour->getManagerRole());
        
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($adminRole);
        $manager->persist($admin);
        $repositoryFive = $wsCreatorService->createWorkspace($config, $wsCreator);
        $repositoryFive->setType('user_repository');
        $admin->addRole($repositoryFive->getManagerRole());
        
        $manager->persist($user);
        $manager->persist($secondUser);
        $manager->persist($thirdUser);
        $manager->persist($wsCreator);
        $manager->persist($admin);
        $manager->persist($repositoryOne);
        $manager->persist($repositoryTwo);
        $manager->persist($repositoryThree);
        $manager->persist($repositoryFour);
        $manager->persist($repositoryFive);
       
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