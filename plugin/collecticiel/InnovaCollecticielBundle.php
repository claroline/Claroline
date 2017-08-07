<?php

/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:00.
 */

namespace Innova\CollecticielBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Innova\CollecticielBundle\Installation\AdditionalInstaller;

class InnovaCollecticielBundle extends DistributionPluginBundle
{
    public function getRoutingPrefix()
    {
        return 'collecticiel';
    }

    public function getRequiredPlugins()
    {
        return ['Claroline\\AgendaBundle\\ClarolineAgendaBundle'];
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function isActiveByDefault()
    {
        return false;
    }
}
