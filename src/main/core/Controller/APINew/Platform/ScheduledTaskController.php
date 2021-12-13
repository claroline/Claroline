<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/scheduledtask")
 */
class ScheduledTaskController extends AbstractCrudController
{
    use HasUsersTrait;
    use PermissionCheckerTrait;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Task\ScheduledTask';
    }

    public function getName()
    {
        return 'scheduledtask';
    }
}
