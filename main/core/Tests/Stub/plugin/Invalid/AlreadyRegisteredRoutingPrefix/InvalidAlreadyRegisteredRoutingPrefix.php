<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\AlreadyRegisteredRoutingPrefix;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class InvalidAlreadyRegisteredRoutingPrefix extends DistributionPluginBundle
{
    public function getRoutingPrefix()
    {
        // this prefix is already used by the core routing
        return 'admin';
    }
}
