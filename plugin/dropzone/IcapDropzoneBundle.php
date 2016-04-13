<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:00.
 */
namespace Icap\DropzoneBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class IcapDropzoneBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return 'dropzone';
    }
}
