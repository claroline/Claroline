<?php

namespace Claroline\DocumentBundle;

use Claroline\CoreBundle\Library\Plugin\ClarolineTool;

class ClarolineDocumentBundle extends ClarolineTool
{
    public function getRoutingPrefix()
    {
        return "document";
    }
}