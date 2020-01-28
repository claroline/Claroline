<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractDeleteAction;
use Claroline\CoreBundle\Entity\User;

class Delete extends AbstractDeleteAction
{
    public function getAction()
    {
        return ['user', self::MODE_DELETE];
    }

    public function getClass()
    {
        return User::class;
    }
}
