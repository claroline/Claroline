<?php

namespace Claroline\CoreBundle\Manager;

abstract class AbstractManager
{
    public function getEntity($class)
    {
        $class = '\Claroline\CoreBundle\Entity\\' . $class;
        
        return new $class();
    }
}
