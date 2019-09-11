<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

use Claroline\AppBundle\API\Transfer\Action\AbstractCreateAction;

class Create extends AbstractCreateAction
{
    public function getAction()
    {
        return ['group', self::MODE_CREATE];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Group';
    }
}
