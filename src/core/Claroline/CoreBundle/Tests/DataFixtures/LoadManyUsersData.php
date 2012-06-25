<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class LoadManyUsersData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;
    private $config;

    public function __construct()
    {
        $type = Configuration::TYPE_SIMPLE;
        $this->config = new Configuration();
        $this->config->setWorkspaceType($type);
        $this->config->setWorkspaceName("my workspace");
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userRole = $this->getReference('role/user');
        $wsCreatorRole = $this->getReference('role/ws_creator');
        $adminRole = $this->getReference('role/admin');

        for ($i = 0; $i < 100; $i++) {
            $this->createUser($i, $userRole, $manager);
        }

        for ($i; $i < 120; $i++) {
            $this->createUser($i, $wsCreatorRole, $manager);
        }

        for ($i; $i < 125; $i++) {
            $this->createUser($i, $adminRole, $manager);
        }

        $manager->flush();
    }

    protected function createUser($number, $role, ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstName("firstName{$number}");
        $user->setLastName("lastName{$number}");
        $user->setUserName("userName{$number}");
        $user->setPlainPassword("password{$number}");
        $user->addRole($role);
        $manager->persist($user);
        $wsCreatorService = $this->container->get('claroline.workspace.creator');
        $ws = $wsCreatorService->createWorkspace($this->config, $user);
        $ws->setType('user_repository');
        $user->addRole($ws->getManagerRole());
        $user->setPersonnalWorkspace($ws);
        $manager->persist($ws);

        $this->addReference("user/manyUser{$number}", $user);
    }

    public function getOrder()
    {
        return 100;
    }
}