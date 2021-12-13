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

use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    public function getAdditionalInstaller(): ?AdditionalInstallerInterface;

    public function getVersion(): string;
}
