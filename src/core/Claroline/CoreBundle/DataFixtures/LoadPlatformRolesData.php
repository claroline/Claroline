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
     * Loads the four base roles commonly used within the platform :
     * - anonymous user         (fixture ref : role/anonymous)
     * - registered user        (fixture ref : role/user)
     *     - workspace creator  (fixture ref : role/ws_creator)
     *     - administrator      (fixture ref : role/admin)
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userRole = new Role();
        $userRole->setName(PlatformRoles::USER);
        $userRole->setTranslationKey('user');
        $userRole->setType(Role::BASE_ROLE);

        $creatorRole = new Role();
        $creatorRole->setName(PlatformRoles::WS_CREATOR);
        $creatorRole->setTranslationKey('ws_creator');
        $creatorRole->setType(Role::BASE_ROLE);

        $adminRole = new Role();
        $adminRole->setName(PlatformRoles::ADMIN);
        $adminRole->setTranslationKey('admin');
        $adminRole->setType(Role::BASE_ROLE);

        $anonymousRole = new Role();
        $anonymousRole->setName(PlatformRoles::ANONYMOUS);
        $anonymousRole->setTranslationKey('anonymous');
        $anonymousRole->setType(Role::BASE_ROLE);

        $manager->persist($anonymousRole);
        $manager->persist($userRole);
        $manager->persist($creatorRole);
        $manager->persist($adminRole);
        $manager->flush();

        $this->addReference('role/anonymous', $anonymousRole);
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