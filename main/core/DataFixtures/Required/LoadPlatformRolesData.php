<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Platform roles data fixture.
 */
class LoadPlatformRolesData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var RoleManager */
    private $roleManager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->roleManager = $container->get('claroline.manager.role_manager');
    }

    /**
     * Loads the four base roles commonly used within the platform :
     * - anonymous user         (fixture ref : role/anonymous)
     * - registered user        (fixture ref : role/user)
     * - workspace creator      (fixture ref : role/ws_creator)
     * - administrator          (fixture ref : role/admin).
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->roleManager->createBaseRole(PlatformRoles::USER, 'user', true, true);
        $this->roleManager->createBaseRole(PlatformRoles::WS_CREATOR, 'ws_creator');
        $this->roleManager->createBaseRole(PlatformRoles::ADMIN, 'admin');
        $this->roleManager->createBaseRole(PlatformRoles::ANONYMOUS, 'anonymous');
        $this->roleManager->createBaseRole('ROLE_HOME_MANAGER', 'home_manager');
        $this->roleManager->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');
    }

    public function getOrder()
    {
        return 1;
    }
}
