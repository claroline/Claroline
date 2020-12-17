<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Symfony\Component\Routing\Annotation\Route;

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
