<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractUpdateAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
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
