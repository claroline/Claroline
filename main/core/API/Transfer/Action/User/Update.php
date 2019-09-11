<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractUpdateAction;

class Update extends AbstractUpdateAction
{
    public function getAction()
    {
        return ['user', self::MODE_UPDATE];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }
}
