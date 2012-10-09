<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;

class LoadManyGroupsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var ArrayCollection $roles */
    private $roles;

    /** @var ArrayCollection $roles */
    private $users;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();

        $this->roles[1] = $this->getReference('role/role_a');
        $this->roles[2] = $this->getReference('role/role_b');
        $this->roles[3] = $this->getReference('role/role_c');
        $this->roles[4] = $this->getReference('role/role_d');
        $this->roles[5] = $this->getReference('role/role_e');
        $this->roles[6] = $this->getReference('role/role_f');

        for ($i = 0; $i < 30; $i++) {
            $this->users[$i] = $this->getReference("user/manyUser{$i}");
        }

        for ($i = 1; $i < 15; $i++) {
            $arrUsers = $this->genArrayUsers($i);
            $arrRoles = $this->genArrayRole(1);
            $this->createGroup($i, $arrRoles, $arrUsers, $manager);
        }

        //TODO add many group to more workspaces than workpace/ws_a
        $this->getReference('group/manyGroup1')->addRole($this->getReference('workspace/ws_a')->getCollaboratorRole());
        $manager->flush();
    }

    protected function createGroup($number, ArrayCollection $roles, ArrayCollection $users, ObjectManager $manager)
    {
        $group = new Group();
        $group->setName("group_{$number}");

        for ($i = 0; $i < $users->count(); $i++) {
            $group->addUser($users[$i]);
        }

        for ($i = 0; $i < $roles->count(); $i++) {
            $group->addRole($roles[$i]);
        }

        $manager->persist($group);
        $this->addReference("group/manyGroup{$number}", $group);
        $manager->flush();
    }

    protected function genArrayUsers($nbIteration)
    {
        $arrUsers = new ArrayCollection();
        $j = 0;

        for ($i = 0; $i < $this->users->count(); $i++) {
            if (($i % $nbIteration) == 0) {
                $arrUsers[$j] = $this->users[$i];
                $j++;
            }
        }

        return $arrUsers;
    }

    //TODO change that one~ they currently don't have any role~ see line 49
    protected function genArrayRole($nbRoles)
    {
        $arrRoles = new ArrayCollection();

        for ($i = 0; $i < $nbRoles; $i++) {
            $arrRoles[$i] = $this->roles[1];
        }

        return $arrRoles;
    }

    public function getOrder()
    {
        return 101;
    }
}