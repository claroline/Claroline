<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractCreateAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class Create extends AbstractCreateAction
{
    public function getAction()
    {
        return ['user', self::MODE_CREATE];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }
}
