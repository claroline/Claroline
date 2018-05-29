<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\LinkBundle\Installation\AdditionalInstaller;

class ClarolineLinkBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
