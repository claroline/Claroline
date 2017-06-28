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

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\CursusBundle\Library\Installation\AdditionalInstaller;

class ClarolineCursusBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return true;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }

    public function getRequiredPlugins()
    {
        return [
            'Claroline\\MessageBundle\\ClarolineMessageBundle',
            'Claroline\\TagBundle\\ClarolineTagBundle',
            'FormaLibre\\ReservationBundle\\FormaLibreReservationBundle',
        ];
    }
}
