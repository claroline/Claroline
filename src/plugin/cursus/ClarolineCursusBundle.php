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

use Claroline\CursusBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineCursusBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller($this->getUpdaterServiceLocator());
    }

    public function getRequiredFixturesDirectory(string $environment): ?string
    {
        return 'DataFixtures/Required';
    }

    public function getRequiredPlugins()
    {
        return [
            'Claroline\\MessageBundle\\ClarolineMessageBundle',
            'Claroline\\TagBundle\\ClarolineTagBundle',
        ];
    }
}
