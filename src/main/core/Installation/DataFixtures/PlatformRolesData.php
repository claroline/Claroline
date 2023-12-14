<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures;

use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\InstallationBundle\Fixtures\PreInstallInterface;
use Claroline\InstallationBundle\Fixtures\PreUpdateInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PlatformRolesData extends AbstractFixture implements PreInstallInterface, PreUpdateInterface, ContainerAwareInterface
{
    private RoleManager $roleManager;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->roleManager = $container->get('claroline.manager.role_manager');
    }

    public function getOrder(): int
    {
        return 2;
    }

    /**
     * Loads the four base roles commonly used within the platform :
     * - anonymous user    (fixture ref : role/anonymous)
     * - registered user   (fixture ref : role/user)
     * - workspace creator (fixture ref : role/ws_creator)
     * - administrator     (fixture ref : role/admin).
     */
    public function load(ObjectManager $manager): void
    {
        if (!$this->roleManager->getRoleByName(PlatformRoles::USER)) {
            $this->roleManager->createBaseRole(PlatformRoles::USER, 'user', true, true);
        }

        if (!$this->roleManager->getRoleByName(PlatformRoles::WS_CREATOR)) {
            $this->roleManager->createBaseRole(PlatformRoles::WS_CREATOR, 'ws_creator');
        }

        if (!$this->roleManager->getRoleByName(PlatformRoles::ADMIN)) {
            $this->roleManager->createBaseRole(PlatformRoles::ADMIN, 'admin');
        }

        if (!$this->roleManager->getRoleByName(PlatformRoles::ANONYMOUS)) {
            $this->roleManager->createBaseRole(PlatformRoles::ANONYMOUS, 'anonymous');
        }

        if (!$this->roleManager->getRoleByName('ROLE_HOME_MANAGER')) {
            $this->roleManager->createBaseRole('ROLE_HOME_MANAGER', 'home_manager');
        }
    }
}
