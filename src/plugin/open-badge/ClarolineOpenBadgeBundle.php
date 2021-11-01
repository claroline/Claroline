<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\OpenBadgeBundle\Installation\AdditionalInstaller;

class ClarolineOpenBadgeBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredFixturesDirectory(string $environment): string
    {
        return 'DataFixtures/Required';
    }
}
