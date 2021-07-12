<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DolibarrBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineDolibarrBundle extends DistributionPluginBundle
{
    public function hasMigrations(): bool
    {
        return false;
    }
}
