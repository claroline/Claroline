<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Role;

class LoadRoleData extends AbstractFixture  implements OrderedFixtureInterface
{
    /**
     * Creates two hierarchies of roles with the following structures :
     * 
     * Role A
     *      Role B
     * 
     * Role C
     *      Role D
     *      Role E
     *          Role F
     */
    public function load(ObjectManager $manager)
    {
        $roleA = new Role();
        $roleA->setName('ROLE_A');
        $roleB = new Role();
        $roleB->setName('ROLE_B');
        $roleC = new Role();
        $roleC->setName('ROLE_C');
        $roleD = new Role();
        $roleD->setName('ROLE_D');
        $roleE = new Role();
        $roleE->setName('ROLE_E');
        $roleF = new Role();
        $roleF->setName('ROLE_F');
        
        $roleB->setParent($roleA);
        $roleD->setParent($roleC);
        $roleE->setParent($roleC);
        $roleF->setParent($roleE);
        
        $manager->persist($roleA);
        $manager->persist($roleB);
        $manager->persist($roleC);
        $manager->persist($roleD);
        $manager->persist($roleE);
        $manager->persist($roleF);
        $manager->flush();

        $this->addReference('role/role_a', $roleA);
        $this->addReference('role/role_b', $roleB);
        $this->addReference('role/role_c', $roleC);
        $this->addReference('role/role_d', $roleD);
        $this->addReference('role/role_e', $roleE);
        $this->addReference('role/role_f', $roleF);
    }
    
    public function getOrder()
    {
        return 3;
    }
}