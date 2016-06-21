<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class ClarolineMigrationBundle extends Bundle implements AutoConfigurableInterface
{
    public function supports($environement)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        return new ConfigurationBuilder();
    }
}
