<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

/**
 * Platform roles data fixture.
 */
class LoadPlatformRolesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads three roles used within the platform :
     * - simple user
     * - workspace creator
     * - administrator
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userRole = new Role();
        $userRole->setName(PlatformRoles::USER);
        $userRole->setTranslationKey('user');
        $userRole->setRoleType(Role::BASE_ROLE);

        $creatorRole = new Role();
        $creatorRole->setName(PlatformRoles::WS_CREATOR);
        $creatorRole->setParent($userRole);
        $creatorRole->setTranslationKey('ws_creator');
        $creatorRole->setRoleType(Role::BASE_ROLE);

        $adminRole = new Role();
        $adminRole->setName(PlatformRoles::ADMIN);
        $adminRole->setParent($creatorRole);
        $adminRole->setTranslationKey('admin');
        $adminRole->setRoleType(Role::BASE_ROLE);
/*
        $customRole = new Role();
        $customRole->setName('ROLE_A');
        $customRole->setTranslationKey('roleA');
        $customRole->setRoleType(Role::CUSTOM_ROLE);
*/
        $manager->persist($userRole);
        $manager->persist($creatorRole);
        $manager->persist($adminRole);
//        $manager->persist($customRole);
        $manager->flush();

        $this->addReference('role/user', $userRole);
        $this->addReference('role/ws_creator', $creatorRole);
        $this->addReference('role/admin', $adminRole);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}