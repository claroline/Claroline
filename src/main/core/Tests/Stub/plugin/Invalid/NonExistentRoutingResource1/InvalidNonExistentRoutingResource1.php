<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\NonExistentRoutingResource1;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class InvalidNonExistentRoutingResource1 extends DistributionPluginBundle
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}
