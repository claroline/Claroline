<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Installation\Updater\Updater130015;
use Claroline\CoreBundle\Installation\Updater\Updater130023;
use Claroline\CoreBundle\Installation\Updater\Updater130025;
use Claroline\CoreBundle\Installation\Updater\Updater130032;
use Claroline\CoreBundle\Installation\Updater\Updater130037;
use Claroline\CoreBundle\Installation\Updater\Updater130100;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.0.15' => Updater130015::class,
            '13.0.23' => Updater130023::class,
            '13.0.25' => Updater130025::class,
            '13.0.32' => Updater130032::class,
            '13.0.37' => Updater130037::class,
            '13.1.0' => Updater130100::class,
        ];
    }

    public function postInstall()
    {
        parent::postInstall();

        $this->setInstallationDate();
        $this->setUpdateDate();
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        parent::postUpdate($currentVersion, $targetVersion);

        $this->setUpdateDate();
    }

    public function end($currentVersion, $targetVersion)
    {
        $this->updateRolesAdmin();
        $this->buildDefaultWorkspaces();
    }

    private function setInstallationDate()
    {
        /** @var PlatformConfigurationHandler $ch */
        $ch = $this->container->get(PlatformConfigurationHandler::class);

        $ch->setParameter('meta.created', DateNormalizer::normalize(new \DateTime()));
    }

    private function setUpdateDate()
    {
        /** @var PlatformConfigurationHandler $ch */
        $ch = $this->container->get(PlatformConfigurationHandler::class);

        $ch->setParameter('meta.updated', DateNormalizer::normalize(new \DateTime()));
    }

    private function updateRolesAdmin()
    {
        $this->log('Update Roles Admin');

        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');

        /** @var Role $adminOrganization */
        $adminOrganization = $om->getRepository(Role::class)->findOneByName('ROLE_ADMIN_ORGANIZATION');

        if (!$adminOrganization) {
            $adminOrganization = $this->container->get('claroline.manager.role_manager')->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');
        }

        /** @var AdminTool $userManagement */
        $userManagement = $om->getRepository(AdminTool::class)->findOneByName('community');
        $userManagement->addRole($adminOrganization);

        $om->persist($userManagement);
        $om->flush();
    }

    private function buildDefaultWorkspaces()
    {
        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($this->logger);

        if (!$om->getRepository(Workspace::class)->findOneBy(['code' => 'default_workspace', 'personal' => false, 'model' => true])) {
            $this->log('Build default workspace');
            $workspaceManager->getDefaultModel(false, true);
        }

        if (!$om->getRepository(Workspace::class)->findOneBy(['code' => 'default_personal', 'personal' => true, 'model' => true])) {
            $this->log('Build default personal workspace');
            $workspaceManager->getDefaultModel(true, true);
        }
    }
}
