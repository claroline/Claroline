<?php

/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:00.
 */

namespace Icap\DropzoneBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Icap\DropzoneBundle\Library\Installation\AdditionalInstaller;

class IcapDropzoneBundle extends DistributionPluginBundle
{
    public function getRoutingPrefix()
    {
        return 'dropzone';
    }

    public function getRequiredPlugins()
    {
        return [
            'Claroline\\AgendaBundle\\ClarolineAgendaBundle',
            'Icap\\NotificationBundle\\IcapNotificationBundle',
        ];
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
