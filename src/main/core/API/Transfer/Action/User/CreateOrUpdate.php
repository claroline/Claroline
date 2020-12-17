<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractCreateOrUpdateAction;
use Claroline\CoreBundle\Entity\User;

class CreateOrUpdate extends AbstractCreateOrUpdateAction
{
    public function getAction()
    {
        return ['user', 'create_or_update'];
    }

    public function getClass()
    {
        return User::class;
    }
}
