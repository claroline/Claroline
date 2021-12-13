<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Additional;

use Psr\Log\LoggerAwareInterface;

interface AdditionalInstallerInterface extends LoggerAwareInterface
{
    public function setEnvironment($environment);

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void;

    public function shouldReplayUpdaters(): bool;

    public function preInstall();

    public function postInstall();

    public function preUpdate($currentVersion, $targetVersion);

    public function postUpdate($currentVersion, $targetVersion);

    public function preUninstall();

    public function postUninstall();

    public function end($currentVersion, $targetVersion);

    /**
     * @return string[] An array of Updater service identifiers (i.e. FQCN) indexed by version
     */
    public static function getUpdaters(): array;

    /**
     * Checks if the plugin have DB migrations that should be played in the install/update process ?
     */
    public function hasMigrations(): bool;

    /**
     * Checks if the bundle has Fixtures that shopuld be loaded in the install/update process.
     *
     * You can use PreInstallInterface / PostInstallInterface / PreUpdateInterface / PostUpdateInterface
     * to choose when you fixtures should be loaded.
     */
    public function hasFixtures(): bool;
}
