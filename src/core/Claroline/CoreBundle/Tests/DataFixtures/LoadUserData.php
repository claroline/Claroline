<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
        $repositoryOne->setType(AbstractWorkspace::USER_REPOSITORY);
        $user->addRole($repositoryOne->getManagerRole());
        $user->setPersonnalWorkspace($repositoryOne);

        $secondUser = new User();
        $secondUser->setFirstName('Bob');
        $secondUser->setLastName('Doe');
        $secondUser->setUserName('user_2');
        $secondUser->setPlainPassword('123');
        $secondUser->addRole($userRole);
        $manager->persist($secondUser);
        $repositoryTwo = $wsCreatorService->createWorkspace($config, $secondUser);
        $repositoryTwo->setType(AbstractWorkspace::USER_REPOSITORY);
        $secondUser->addRole($repositoryTwo->getManagerRole());
        $secondUser->setPersonnalWorkspace($repositoryTwo);

        $thirdUser = new User();
        $thirdUser->setFirstName('Bill');
        $thirdUser->setLastName('Doe');
        $thirdUser->setUserName('user_3');
        $thirdUser->setPlainPassword('123');
        $thirdUser->addRole($userRole);
        $manager->persist($thirdUser);
        $repositoryThree = $wsCreatorService->createWorkspace($config, $thirdUser);
        $repositoryThree->setType(AbstractWorkspace::USER_REPOSITORY);
        $thirdUser->addRole($repositoryThree->getManagerRole());
        $thirdUser->setPersonnalWorkspace($repositoryThree);

        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($wsCreatorRole);
        $manager->persist($wsCreator);
        $repositoryFour = $wsCreatorService->createWorkspace($config, $wsCreator);
        $repositoryFour->setType(AbstractWorkspace::USER_REPOSITORY);
        $wsCreator->addRole($repositoryFour->getManagerRole());
        $wsCreator->setPersonnalWorkspace($repositoryFour);

        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($adminRole);
        $manager->persist($admin);
        $repositoryFive = $wsCreatorService->createWorkspace($config, $wsCreator);
        $repositoryFive->setType(AbstractWorkspace::USER_REPOSITORY);
        $admin->addRole($repositoryFive->getManagerRole());
        $admin->setPersonnalWorkspace($repositoryFive);

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