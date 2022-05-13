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

use Claroline\CoreBundle\Installation\Updater\Updater130015;
use Claroline\CoreBundle\Installation\Updater\Updater130023;
use Claroline\CoreBundle\Installation\Updater\Updater130025;
use Claroline\CoreBundle\Installation\Updater\Updater130032;
use Claroline\CoreBundle\Installation\Updater\Updater130037;
use Claroline\CoreBundle\Installation\Updater\Updater130100;
use Claroline\CoreBundle\Installation\Updater\Updater130300;
use Claroline\CoreBundle\Installation\Updater\Updater130303;
use Claroline\CoreBundle\Installation\Updater\Updater130406;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineCoreInstaller extends AdditionalInstaller
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
            '13.3.0' => Updater130300::class,
            '13.3.3' => Updater130303::class,
            '13.4.6' => Updater130406::class,
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
