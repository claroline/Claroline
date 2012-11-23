<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\User;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $usernames;

    public function __construct($usernames = null)
    {
        if ($usernames != null){
            $this->usernames = $usernames;
        } else {
            $this->usernames = array('admin', 'ws_creator', 'user', 'user_2', 'user_3');
        }
    }

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
            'user' => array('Jane', 'Doe', 'user', '123', $userRole),
            'user_2' => array('Bob', 'Doe', 'user_2', '123', $userRole),
            'user_3' => array('Bill', 'Doe', 'user_3', '123', $userRole),
            'ws_creator' => array('Henry', 'Doe', 'ws_creator', '123', $wsCreatorRole),
            'admin' => array('John', 'Doe', 'admin', '123', $adminRole)
        );

        foreach ($this->usernames as $username) {
            if(array_key_exists($username, $users)){
                $user = new User();
                $user->setFirstName($users[$username][0]);
                $user->setLastName($users[$username][1]);
                $user->setUserName($users[$username][2]);
                $user->setPlainPassword($users[$username][3]);
                $user->addRole($users[$username][4]);
                $user = $this->container->get('claroline.user.creator')->create($user);
                $this->addReference("user/{$users[$username][2]}", $user);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}