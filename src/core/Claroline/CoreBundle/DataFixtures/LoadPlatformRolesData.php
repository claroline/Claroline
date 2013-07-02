<?php

namespace Claroline\CoreBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

/**
 * Platform roles data fixture.
 */
class LoadPlatformRolesData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        $roleManager = $this->container->get('claroline.manager.role_manager');

        $userRole = $roleManager->createBaseRole(PlatformRoles::USER, 'user');
        $creatorRole = $roleManager->createBaseRole(PlatformRoles::WS_CREATOR, 'ws_creator');
        $adminRole = $roleManager->createBaseRole(PlatformRoles::ADMIN, 'admin');
        $anonymousRole = $roleManager->createBaseRole(PlatformRoles::ANONYMOUS, 'anonymous');

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