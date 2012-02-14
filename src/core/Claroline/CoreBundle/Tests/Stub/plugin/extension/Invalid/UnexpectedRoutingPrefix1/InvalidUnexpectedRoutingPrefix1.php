<?php

namespace Invalid\UnexpectedRoutingPrefix1;

use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;

class InvalidUnexpectedRoutingPrefix1 extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        return array();
    }
}