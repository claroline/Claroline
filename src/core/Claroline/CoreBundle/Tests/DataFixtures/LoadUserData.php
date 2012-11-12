<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Role;
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

        $users = array(
            array('Jane', 'Doe', 'user', '123', $userRole),
            array('Bob', 'Doe', 'user_2', '123', $userRole),
            array('Bill', 'Doe', 'user_3', '123', $userRole),
            array('Henry', 'Doe', 'ws_creator', '123', $wsCreatorRole),
            array('John', 'Doe', 'admin', '123', $adminRole)
        );

        foreach ($users as $userProps) {
            $user = new User();
            $user->setFirstName($userProps[0]);
            $user->setLastName($userProps[1]);
            $user->setUserName($userProps[2]);
            $user->setPlainPassword($userProps[3]);
            $user->addRole($userProps[4]);
            $user = $this->container->get('claroline.user.creator')->create($user);
            $this->addReference("user/{$userProps[2]}", $user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}