<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Task\ScheduledTask")
 * @Route("/scheduledtask")
 */
class ScheduledTaskController extends AbstractCrudController
{
    public function getName()
    {
        return 'scheduledtask';
    }
}
