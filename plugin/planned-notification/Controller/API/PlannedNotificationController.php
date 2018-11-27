<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Controller\API;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/plannednotification")
 */
class PlannedNotificationController extends AbstractCrudController
{
    /* @var PlannedNotificationManager */
    protected $manager;

    /**
     * PlannedNotificationController constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.planned_notification_manager")
     * })
     *
     * @param PlannedNotificationManager $manager
     */
    public function __construct(PlannedNotificationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'planned_notification';
    }

    public function getClass()
    {
        return PlannedNotification::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/list",
     *     name="apiv2_plannednotification_workspace_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function plannedNotificationsListAction(Workspace $workspace, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        $data = $this->finder->search('Claroline\PlannedNotificationBundle\Entity\PlannedNotification', $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/roles/list",
     *     name="apiv2_plannednotification_workspace_roles_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function workspaceRolesListAction(Workspace $workspace, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['type'] = 'workspace';
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        $data = $this->finder->search('Claroline\CoreBundle\Entity\Role', $params, [Options::SERIALIZE_MINIMAL]);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/manual/notifications/trigger",
     *     name="apiv2_plannednotification_manual_notifications_trigger"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function manualNotificationsTriggerAction(Request $request)
    {
        $data = $this->decodeRequest($request);
        $tasks = $this->manager->generateManualScheduledTasks($data);

        if (0 < count($tasks)) {
            return new JsonResponse();
        } else {
            return new JsonResponse(null, 422);
        }
    }
}
