<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:00
 */

namespace ICAP\DropZoneBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class ICAPDropZoneBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "dropzone";
    }
}
