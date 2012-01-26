<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Role;

class LoadPlatformRolesData extends AbstractFixture implements OrderedFixtureInterface
{    
    public function load($manager)
    {
        $userRole = new Role();
        $userRole->setName('ROLE_USER');
        
        $creatorRole = new Role();
        $creatorRole->setName('ROLE_WS_CREATOR');
        $creatorRole->setParent($userRole);
        
        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');
        $adminRole->setParent($creatorRole);
        
        $manager->persist($userRole);
        $manager->persist($creatorRole);
        $manager->persist($adminRole);
        $manager->flush();
        
        $this->addReference('role/user', $userRole);
        $this->addReference('role/ws_creator', $creatorRole);
        $this->addReference('role/admin', $adminRole);
    }
    
    public function getOrder()
    {
        return 1;
    }
}