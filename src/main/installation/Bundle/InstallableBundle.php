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
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class InstallableBundle extends Bundle implements InstallableInterface
{
    public function getAdditionalInstaller(): ?AdditionalInstallerInterface
    {
        $class = $this->getInstallerClass();
        if (class_exists($class)) {
            return new $class($this->container->get('claroline.updater_locator'));
        }

        return null;
    }

    /**
     * Returns the bundle's installer class.
     */
    protected function getInstallerClass(): string
    {
        $basename = preg_replace('/Bundle$/', '', $this->getName());

        return $this->getNamespace().'\\Installation\\'.$basename.'Installer';
    }
}
