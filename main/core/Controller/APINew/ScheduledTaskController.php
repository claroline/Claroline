<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/scheduledtask")
 */
class ScheduledTaskController extends AbstractCrudController
{
    use HasUsersTrait;

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Task\ScheduledTask';
    }

    public function getName()
    {
        return 'scheduledtask';
    }
}
