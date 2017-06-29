<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\ScheduledTaskManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('tasks_scheduling')")
 */
class ScheduledTaskController extends Controller
{
    private $apiManager;
    private $configHandler;
    private $groupManager;
    private $request;
    private $serializer;
    private $taskManager;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "apiManager"       = @DI\Inject("claroline.manager.api_manager"),
     *     "configHandler"    = @DI\Inject("claroline.config.platform_config_handler"),
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "serializer"       = @DI\Inject("jms_serializer"),
     *     "taskManager"      = @DI\Inject("claroline.manager.scheduled_task_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        PlatformConfigurationHandler $configHandler,
        GroupManager $groupManager,
        Request $request,
        Serializer $serializer,
        ScheduledTaskManager $taskManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->apiManager = $apiManager;
        $this->configHandler = $configHandler;
        $this->groupManager = $groupManager;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->taskManager = $taskManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/management",
     *     name="claro_admin_scheduled_tasks_management",
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     */
    public function scheduledTasksManagementAction()
    {
        $isCronConfigured = $this->configHandler->hasParameter('is_cron_configured') ?
            $this->configHandler->getParameter('is_cron_configured') :
            false;
        $tasksData = $this->taskManager->searchTasksPartialList([], 0, 20);

        return [
            'isCronConfigured' => $isCronConfigured ? 1 : 0,
            'tasks' => $tasksData['tasks'],
            'total' => $tasksData['count'],
        ];
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/limit/{limit}/search",
     *     name="claro_admin_scheduled_tasks_search",
     *     options = {"expose"=true}
     * )
     */
    public function scheduledTasksSearchAction($page, $limit)
    {
        $searches = $this->request->query->all();
        $data = $this->taskManager->searchTasksPartialList($searches, $page, $limit);
        $content = [
            'tasks' => $this->serializer->serialize(
                $data['tasks'],
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            ),
            'total' => $data['count'],
        ];

        return new JsonResponse($content, 200);
    }

    /**
     * @EXT\Route(
     *     "/create",
     *     name="claro_admin_scheduled_task_create",
     *     options = {"expose"=true}
     * )
     */
    public function scheduledTaskCreateAction()
    {
        $type = $this->request->get('type', false);
        $scheduledDate = $this->request->get('scheduledDate', false) ?
            new \DateTime($this->request->get('scheduledDate')) :
            null;
        $data = $this->request->get('data', false) ? json_decode($this->request->get('data'), true) : null;
        $name = $this->request->get('name', false) ? $this->request->get('name') : null;
        $usersIds = $this->request->get('users', false) ? explode(',', $this->request->get('users')) : null;
        $users = $usersIds ? $this->userManager->getUsersByIds($usersIds) : [];
        $groupId = $this->request->get('group', false);
        $group = $groupId ? $this->groupManager->getGroupById($groupId) : null;
        $workspaceId = $this->request->get('workspace', false);
        $workspace = $workspaceId ? $this->workspaceManager->getWorkspaceById($workspaceId) : null;
        $task = $this->taskManager->createScheduledTask($type, $scheduledDate, $data, $name, $users, $group, $workspace);
        $serializedTask = $this->serializer->serialize(
            $task,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedTask, 200);
    }

    /**
     * @EXT\Route(
     *     "/{task}/edit",
     *     name="claro_admin_scheduled_task_edit",
     *     options = {"expose"=true}
     * )
     */
    public function scheduledTaskEditAction(ScheduledTask $task)
    {
        $scheduledDate = $this->request->get('scheduledDate', false) ?
            new \DateTime($this->request->get('scheduledDate')) :
            null;
        $data = $this->request->get('data', false) ? json_decode($this->request->get('data'), true) : null;
        $name = $this->request->get('name', false) ? $this->request->get('name') : null;
        $usersIds = $this->request->get('users', false) ? explode(',', $this->request->get('users')) : null;
        $users = $usersIds ? $this->userManager->getUsersByIds($usersIds) : [];
        $groupId = $this->request->get('group', false);
        $group = $groupId ? $this->groupManager->getGroupById($groupId) : null;
        $workspaceId = $this->request->get('workspace', false);
        $workspace = $workspaceId ? $this->workspaceManager->getWorkspaceById($workspaceId) : null;
        $this->taskManager->editScheduledTask($task, $scheduledDate, $data, $name, $users, $group, $workspace);
        $serializedTask = $this->serializer->serialize(
            $task,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedTask, 200);
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="claro_admin_scheduled_tasks_delete",
     *     options = {"expose"=true}
     * )
     */
    public function scheduledTasksDeleteAction()
    {
        $tasks = $this->apiManager->getParameters('ids', 'Claroline\CoreBundle\Entity\Task\ScheduledTask');
        $this->taskManager->deleteScheduledTasks($tasks);

        return new JsonResponse('success', 200);
    }
}
