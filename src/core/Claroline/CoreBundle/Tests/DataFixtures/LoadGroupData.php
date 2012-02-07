<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Group;

class LoadGroupData extends AbstractFixture
{
    /**
     * Loads three groups with the following roles :
     * 
     * Group A : ROLE_A
     * Group B : ROLE_D (i.e. ROLE_C -> ROLE_D)
     * Group C : ROLE_F (i.e. ROLE_C -> ROLE_E -> ROLE_F)
     */
    public function load($manager)
    {
        $groupA = new Group();
        $groupA->setName('Group A');
        $groupA->addRole($this->getReference('role/role_a'));
        $groupB = new Group();
        $groupB->setName(('Group B'));
        $groupB->addRole($this->getReference('role/role_d'));
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
}