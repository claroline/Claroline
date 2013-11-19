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

use Claroline\InstallationBundle\Bundle\BundleVersion;

interface AdditionalInstallerInterface
{
    public function setEnvironment($environment);
    public function setLogger(\Closure $logger);
    public function preInstall();
    public function postInstall();
    public function preUpdate($currentVersion, $targetVersion);
    public function postUpdate($currentVersion, $targetVersion);
    public function preUninstall();
    public function postUninstall();
}
