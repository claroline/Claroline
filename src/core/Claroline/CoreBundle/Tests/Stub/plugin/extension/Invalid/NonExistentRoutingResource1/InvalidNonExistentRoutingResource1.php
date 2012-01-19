<?php

namespace Invalid\NonExistentRoutingResource1;

use Claroline\CoreBundle\AbstractType\ClarolineExtension;

class InvalidNonExistentRoutingResource1 extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}