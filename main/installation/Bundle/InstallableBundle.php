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
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class InstallableBundle extends Bundle implements InstallableInterface
{
    public function hasMigrations()
    {
        return true;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return;
    }

    public function getPostInstallFixturesDirectory($environment)
    {
        return;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return;
    }

    public function getAdditionalInstaller()
    {
        return;
    }

    public function getUpdaterServiceLocator(): ContainerInterface
    {
        return $this->container->get('claroline.updater_locator');
    }
}
