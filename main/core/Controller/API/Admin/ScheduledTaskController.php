<?php

namespace Claroline\CoreBundle\Controller\API\Admin;

use Claroline\CoreBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * JSON API for scheduled task management.
 *
 * @EXT\Route("/scheduled-tasks", options={"expose"=true})
 * @SEC\PreAuthorize("canOpenAdminTool('tasks_scheduling')")
 */
class ScheduledTaskController
{
    /** @var ApiManager */
    private $apiManager;

    /** @var FinderProvider */
    private $finder;

    /** @var ScheduledTaskManager */
    private $manager;

    /**
     * ScheduledTaskController constructor.
     *
     * @DI\InjectParams({
     *     "apiManager" = @DI\Inject("claroline.manager.api_manager"),
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "manager"    = @DI\Inject("claroline.manager.scheduled_task_manager")
     * })
     *
     * @param ApiManager           $apiManager
     * @param FinderProvider       $finder
     * @param ScheduledTaskManager $manager
     */
    public function __construct(
        ApiManager $apiManager,
        FinderProvider $finder,
        ScheduledTaskManager $manager)
    {
        $this->apiManager = $apiManager;
        $this->finder = $finder;
        $this->manager = $manager;
    }

    /**
     * @EXT\Route("", name="claro_scheduled_task_list")
     * @EXT\Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search(
                'Claroline\CoreBundle\Entity\Task\ScheduledTask',
                $request->query->all()
            )
        );
    }

    /**
     * @EXT\Route("", name="claro_scheduled_task_create")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        try {
            $scheduledTask = $this->manager->create(json_decode($request->getContent(), true));

            return new JsonResponse(
                $this->manager->serialize($scheduledTask),
                201
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @EXT\Route("/{id}", name="claro_scheduled_task_update")
     * @EXT\Method("PUT")
     *
     * @param ScheduledTask $scheduledTask
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function updateAction(ScheduledTask $scheduledTask, Request $request)
    {
        try {
            $this->manager->update(json_decode($request->getContent(), true), $scheduledTask);

            return new JsonResponse(
                $this->manager->serialize($scheduledTask)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @EXT\Route("", name="claro_scheduled_tasks_delete")
     * @EXT\Method("DELETE")
     *
     * @return JsonResponse
     */
    public function deleteBulkAction()
    {
        try {
            $this->manager->deleteBulk(
                $this->apiManager->getParameters('ids', 'Claroline\CoreBundle\Entity\Task\ScheduledTask')
            );

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }
}
