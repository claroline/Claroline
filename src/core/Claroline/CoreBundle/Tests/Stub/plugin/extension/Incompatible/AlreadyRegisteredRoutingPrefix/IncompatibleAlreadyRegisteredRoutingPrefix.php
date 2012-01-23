<?php

namespace Incompatible\AlreadyRegisteredRoutingPrefix;

use Claroline\CoreBundle\Plugin\ClarolineExtension;

class IncompatibleAlreadyRegisteredRoutingPrefix extends ClarolineExtension
{
    public function getRoutingPrefix()
    {
        //this prefix will be already registered in a test (see CommonCheckerTest)        
        return 'sharedPrefix';
    }
}