<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Bundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    /**
     * Checks if the plugin have DB migrations that should be played in the install/update process ?
     */
    public function hasMigrations(): bool;

    /**
     * Returns the directory path (inside the bundle) where required fixtures are stored.
     *
     * Required fixtures are loaded at each install/update just after the bundle migrations.
     * You MUST ensure the data don't exist before inserting anything.
     * This is useful to ensure some data always exist in the DB (eg. platform roles, default templates).
     */
    public function getRequiredFixturesDirectory(string $environment): ?string;

    /**
     * Returns the directory path (inside the bundle) where post-install fixtures are stored.
     *
     * Post-install fixtures are loaded once at the end of the bundle installation.
     * This is useful to initialize some default data in the DB.
     */
    public function getPostInstallFixturesDirectory(string $environment): ?string;

    public function getAdditionalInstaller();

    public function getUpdaterServiceLocator(): ContainerInterface;

    public function getVersion(): string;
}
