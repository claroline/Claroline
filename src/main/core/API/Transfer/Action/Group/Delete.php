<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

use Claroline\AppBundle\API\Transfer\Action\AbstractDeleteAction;

class Delete extends AbstractDeleteAction
{
    public function getAction()
    {
        return ['group', self::MODE_DELETE];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Group';
    }
}
