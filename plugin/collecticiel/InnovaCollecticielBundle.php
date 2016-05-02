<?php

/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:00.
 */

namespace Innova\CollecticielBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class InnovaCollecticielBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return 'collecticiel';
    }

    public function getPluginsRequirements()
    {
        return ['Claroline\\AgendaBundle\\ClarolineAgendaBundle'];
    }
}
