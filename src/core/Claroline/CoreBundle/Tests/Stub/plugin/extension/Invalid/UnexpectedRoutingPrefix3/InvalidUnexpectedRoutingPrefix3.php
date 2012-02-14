<?php

namespace Invalid\UnexpectedRoutingPrefix3;

use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;

class InvalidUnexpectedRoutingPrefix3 extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}