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

use Claroline\CoreBundle\Installation\Updater\Updater140000;
use Claroline\CoreBundle\Installation\Updater\Updater140010;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineCoreInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '14.0.0' => Updater140000::class,
            '14.0.10' => Updater140010::class,
        ];
    }

    public function hasFixtures(): bool
    {
        return true;
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
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');

        // once everything is installed, we can load default models
        $workspaceManager->getDefaultModel(false);
        $workspaceManager->getDefaultModel(true);
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
}
