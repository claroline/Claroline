<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractUpdateAction;
use Claroline\CoreBundle\Entity\User;

class Update extends AbstractUpdateAction
{
    public function getAction()
    {
        return ['user', self::MODE_UPDATE];
    }

    public function getClass()
    {
        return User::class;
    }
}
