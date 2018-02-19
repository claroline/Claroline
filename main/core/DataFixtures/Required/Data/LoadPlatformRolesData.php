<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PlatformRoles;

/**
 * Platform roles data fixture.
 */
class LoadPlatformRolesData implements RequiredFixture
{
    /**
     * Loads the four base roles commonly used within the platform :
     * - anonymous user         (fixture ref : role/anonymous)
     * - registered user        (fixture ref : role/user)
     *     - workspace creator  (fixture ref : role/ws_creator)
     *     - administrator      (fixture ref : role/admin).
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $manager->startFlushSuite();
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $roleManager->createBaseRole(PlatformRoles::USER, 'user');
        $roleManager->createBaseRole(PlatformRoles::WS_CREATOR, 'ws_creator');
        $roleManager->createBaseRole(PlatformRoles::ADMIN, 'admin');
        $roleManager->createBaseRole(PlatformRoles::ANONYMOUS, 'anonymous');
        $roleManager->createBaseRole('ROLE_HOME_MANAGER', 'home_manager');
        $roleManager->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');

        $manager->endFlushSuite();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 1;
    }
}
