<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads three groups with the following roles :
     * 
     * Group A : ROLE_A
     * Group B : ROLE_D (i.e. ROLE_C -> ROLE_D)
     * Group C : ROLE_F (i.e. ROLE_C -> ROLE_E -> ROLE_F)
     */
    public function load(ObjectManager $manager)
    {
        $groupA = new Group();
        $groupA->setName('Group A');
        $groupA->addRole($this->getReference('role/role_a'));
        $groupA->addUser($this->getReference('user/user'));
        $groupA->addUser($this->getReference('user/user_2'));
        $groupB = new Group();
        $groupB->setName(('Group B'));
        $groupB->addRole($this->getReference('role/role_d'));
        $groupB->addUser($this->getReference('user/user_3'));
        $groupC = new Group();
        $groupC->setName(('Group C'));
        $groupC->addRole($this->getReference('role/role_f'));
        
        $manager->persist($groupA);
        $manager->persist($groupB);
        $manager->persist($groupC);
        $manager->flush();
        
        $this->addReference('group/group_a', $groupA);
        $this->addReference('group/group_b', $groupB);
        $this->addReference('group/group_c', $groupC);
    }
    
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}