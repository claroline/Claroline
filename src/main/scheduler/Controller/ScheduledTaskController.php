<?php

namespace Claroline\SchedulerBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Controller\Model\HasUsersTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Manager\ScheduledTaskManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/scheduled_task", name="apiv2_scheduled_task_")
 */
class ScheduledTaskController extends AbstractCrudController
{
    use HasUsersTrait;
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ScheduledTaskManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return ScheduledTask::class;
    }

    public static function getName(): string
    {
        return 'scheduled_task';
    }

    /**
     * Manually execute a list of scheduled tasks.
     * If no ids is passed, it will execute all eligible tasks.
     *
     * @Route("/execute", name="execute", methods={"POST"})
     */
    public function executeAction(Request $request): JsonResponse
    {
        $tasks = $this->decodeIdsString($request, ScheduledTask::class);
        if (empty($tasks)) {
            $tasks = $this->manager->getTasksToExecute();
        }

        foreach ($tasks as $task) {
            if ($this->checkPermission('EDIT', $task)) {
                $this->manager->execute($task);
            }
        }

        return new JsonResponse(null, 204);
    }
}
