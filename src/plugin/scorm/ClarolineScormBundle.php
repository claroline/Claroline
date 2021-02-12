<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\ScormBundle\Installation\AdditionalInstaller;

class ClarolineScormBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
