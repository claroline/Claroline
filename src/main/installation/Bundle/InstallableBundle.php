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
    public function hasMigrations(): bool
    {
        return true;
    }

    public function getRequiredFixturesDirectory(string $environment): ?string
    {
        return null;
    }

    public function getPostInstallFixturesDirectory(string $environment): ?string
    {
        return null;
    }

    public function getAdditionalInstaller()
    {
        return null;
    }

    public function getUpdaterServiceLocator(): ContainerInterface
    {
        return $this->container->get('claroline.updater_locator');
    }
}
