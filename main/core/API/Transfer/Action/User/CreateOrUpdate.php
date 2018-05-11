<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractCreateOrUpdateAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class CreateOrUpdate extends AbstractCreateOrUpdateAction
{
    public function getAction()
    {
        return ['user', 'create_or_update'];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }
}
