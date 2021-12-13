<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\MessageBundle\ClarolineMessageBundle;
use Claroline\TagBundle\ClarolineTagBundle;

class ClarolineCursusBundle extends DistributionPluginBundle
{
    public function getRequiredPlugins()
    {
        return [
            ClarolineMessageBundle::class,
            ClarolineTagBundle::class,
        ];
    }
}
