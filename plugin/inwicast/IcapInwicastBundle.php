<?php

/*
 * This file is part of the Inwicast plugin for Claroline Connect.
 *
 * (c) INWICAST <dev@inwicast.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\InwicastBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class IcapInwicastBundle extends DistributionPluginBundle
{
    public function isActiveByDefault()
    {
        return false;
    }
}
