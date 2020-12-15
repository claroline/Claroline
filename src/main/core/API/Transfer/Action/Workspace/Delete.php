<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Transfer\Action\AbstractDeleteAction;

class Delete extends AbstractDeleteAction
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\Workspace';
    }

    public function getAction()
    {
        return ['workspace', self::MODE_DELETE];
    }

    public function getBatchSize()
    {
        return 500;
    }
}
