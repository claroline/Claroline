<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractDeleteAction;

class Delete extends AbstractDeleteAction
{
    public function getAction()
    {
        return ['user', self::MODE_DELETE];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }
}
