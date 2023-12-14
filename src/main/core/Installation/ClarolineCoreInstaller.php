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
use Claroline\CoreBundle\Installation\Updater\Updater140014;
use Claroline\CoreBundle\Installation\Updater\Updater140100;
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
            '14.0.14' => Updater140014::class,
            '14.1.0' => Updater140100::class,
        ];
    }

    public function hasMigrations(): bool
    {
        return true;
    }

    public function hasFixtures(): bool
    {
        return true;
    }

    public function postInstall(): void
    {
        parent::postInstall();

        $this->setInstallationDate();
        $this->setUpdateDate();
    }

    public function postUpdate(string $currentVersion, string $targetVersion): void
    {
        parent::postUpdate($currentVersion, $targetVersion);

        $this->setUpdateDate();
    }

    public function end(string $currentVersion = null, string $targetVersion = null): void
    {
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');

        // once everything is installed, we can load default models
        $workspaceManager->getDefaultModel(false);
        $workspaceManager->getDefaultModel(true);
    }

    private function setInstallationDate(): void
    {
        /** @var PlatformConfigurationHandler $ch */
        $ch = $this->container->get(PlatformConfigurationHandler::class);

        $ch->setParameter('meta.created', DateNormalizer::normalize(new \DateTime()));
    }

    private function setUpdateDate(): void
    {
        /** @var PlatformConfigurationHandler $ch */
        $ch = $this->container->get(PlatformConfigurationHandler::class);

        $ch->setParameter('meta.updated', DateNormalizer::normalize(new \DateTime()));
    }
}
