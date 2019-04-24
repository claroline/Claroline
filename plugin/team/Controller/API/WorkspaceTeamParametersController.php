<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/workspaceteamparameters")
 */
class WorkspaceTeamParametersController extends AbstractCrudController
{
    public function getName()
    {
        return 'workspaceteamparameters';
    }

    public function getClass()
    {
        return 'Claroline\TeamBundle\Entity\WorkspaceTeamParameters';
    }

    public function getIgnore()
    {
        return ['create', 'deleteBulk', 'get', 'exist', 'copyBulk', 'schema', 'find', 'list'];
    }
}
