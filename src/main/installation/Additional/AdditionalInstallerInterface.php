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

interface AdditionalInstallerInterface
{
    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void;

    public function shouldReplayUpdaters(): bool;

    public function preInstall(): void;

    public function postInstall(): void;

    public function preUpdate(string $currentVersion, string $targetVersion): void;

    public function postUpdate(string $currentVersion, string $targetVersion);

    public function preUninstall(): void;

    public function postUninstall(): void;

    public function end(string $currentVersion = null, string $targetVersion = null);

    /**
     * @return string[] An array of Updater service identifiers (i.e. Fully Qualified Class Name) indexed by version
     */
    public static function getUpdaters(): array;

    /**
     * Checks if the plugin have DB migrations that should be played in the installation/update process ?
     */
    public function hasMigrations(): bool;

    /**
     * Checks if the bundle has Fixtures that should be loaded in the installation/update process.
     *
     * You can use PreInstallInterface / PostInstallInterface / PreUpdateInterface / PostUpdateInterface
     * to choose when you fixtures should be loaded.
     */
    public function hasFixtures(): bool;
}
