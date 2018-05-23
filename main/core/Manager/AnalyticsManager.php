<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogResourceExportEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.analytics_manager")
 */
class AnalyticsManager
{
    /** @var ResourceNodeRepository */
    private $resourceRepo;

    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;

    /** @var LogRepository */
    private $logRepository;

    /** @var LogManager */
    private $logManager;

    /** @var UserManager */
    private $userManager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var WidgetManager */
    private $widgetManager;

    /** @var StrictDispatcher */
    private $dispatcher;

    /**
     * @DI\InjectParams({
     *     "objectManager"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "logManager"             = @DI\Inject("claroline.log.manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "widgetManager"          = @DI\Inject("claroline.manager.widget_manager"),
     *     "dispatcher"             = @DI\Inject("claroline.event.event_dispatcher")
     * })
     *
     * @param ObjectManager    $objectManager
     * @param LogManager       $logManager
     * @param UserManager      $userManager
     * @param WorkspaceManager $workspaceManager
     * @param WidgetManager    $widgetManager
     * @param StrictDispatcher $dispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        LogManager $logManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        WidgetManager $widgetManager,
        StrictDispatcher $dispatcher
    ) {
        $this->logManager = $logManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->widgetManager = $widgetManager;
        $this->dispatcher = $dispatcher;
        $this->resourceRepo = $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceTypeRepo = $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->logRepository = $objectManager->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function getResourceTypesCount(Workspace $workspace = null, $organizations = null)
    {
        $resourceTypes = $this->resourceTypeRepo->countResourcesByType($workspace, $organizations);
        $chartData = [];
        foreach ($resourceTypes as $type) {
            $chartData["rt-${type['id']}"] = [
                'xData' => $type['name'],
                'yData' => floatval($type['total']),
            ];
        }

        return $chartData;
    }

    public function getOtherResourceTypesCount()
    {
        /** @var \Claroline\CoreBundle\Event\Analytics\PlatformContentItemEvent $event */
        $event = $this->dispatcher->dispatch(
            'administration_analytics_platform_content_item_add',
            'Analytics\PlatformContentItem'
        );

        $resourceTypes = [];
        foreach ($event->getItems() as $type) {
            if (floatval($type['value']) > 0) {
                $resourceTypes['ort-'.$type['item']] = [
                    'id' => $type['item'],
                    'xData' => $type['label'],
                    'yData' => floatval($type['value']),
                ];
            }
        }

        return $resourceTypes;
    }

    public function getDailyActions(array $finderParams = [])
    {
        return $this->logManager->getChartData($this->formatQueryParams($finderParams));
    }

    public function topWorkspaceByAction(array $finderParams)
    {
        $query = $this->formatQueryParams($finderParams);

        if (!isset($query['filters']['action'])) {
            $query['filters']['action'] = LogWorkspaceToolReadEvent::ACTION;
        }
        $queryParams = FinderProvider::parseQueryParams($query);

        return $this->logRepository->findTopWorkspaceByAction($queryParams['allFilters'], $queryParams['limit']);
    }

    public function topResourcesByAction(array $finderParams, $onlyMedia = false)
    {
        $query = $this->formatQueryParams($finderParams);

        if (!isset($query['filters']['action'])) {
            $query['filters']['action'] = LogResourceReadEvent::ACTION;
        }

        if ($onlyMedia) {
            $query['filters']['resourceType'] = 'file';
        }
        $queryParams = FinderProvider::parseQueryParams($query);

        return $this->logRepository->findTopResourcesByAction($queryParams['allFilters'], $queryParams['limit']);
    }

    public function userRolesData($organizations = null)
    {
        return $this->userManager->countUsersForPlatformRoles($organizations);
    }

    public function countNonPersonalWorkspaces($organizations = null)
    {
        return $this->workspaceManager->getNbNonPersonalWorkspaces($organizations);
    }

    public function getWidgetsData($organizations = null)
    {
        $all = floatval($this->widgetManager->getNbWidgetInstances($organizations));
        $ws = floatval($this->widgetManager->getNbWorkspaceWidgetInstances($organizations));
        $desktop = floatval($this->widgetManager->getNbDesktopWidgetInstances($organizations));
        $list = $this->widgetManager->countWidgetsByType($organizations);

        return [
            'all' => $all,
            'workspace' => $ws,
            'desktop' => $desktop,
            'list' => $list,
        ];
    }

    /**
     * Retrieve user who connected at least one time on the application.
     *
     * @param array $finderParams
     * @param bool  $defaultPeriod
     *
     * @return int
     */
    public function countActiveUsers(array $finderParams = [], $defaultPeriod = false)
    {
        if ($defaultPeriod) {
            $finderParams = $this->formatQueryParams($finderParams);
        }
        $queryParams = FinderProvider::parseQueryParams($finderParams);
        $resultData = $this->logRepository->countActiveUsers($queryParams['allFilters']);

        return floatval($resultData);
    }

    private function formatQueryParams(array $finderParams = [])
    {
        $filters = isset($finderParams['filters']) ? $finderParams['filters'] : [];
        $hiddenFilters = isset($finderParams['hiddenFilters']) ? $finderParams['hiddenFilters'] : [];

        return [
            'filters' => $this->formatDateRange($filters),
            'hiddenFilters' => $hiddenFilters,
        ];
    }

    private function formatDateRange(array $filters)
    {
        // Default 30 days analytics
        if (!isset($filters['dateLog'])) {
            $date = new \DateTime('now');
            $date->setTime(0, 0, 0);
            $date->sub(new \DateInterval('P30D'));
            $filters['dateLog'] = clone $date;
        }

        if (!isset($filters['dateTo'])) {
            $date = clone $filters['dateLog'];
            $date->add(new \DateInterval('P30D'));
            $date->setTime(23, 59, 59);
            $filters['dateTo'] = clone $date;
        }

        return $filters;
    }

    public function getTopActions(array $finderParams = [])
    {
        $finderParams['filters'] = isset($finderParams['filters']) ? $finderParams['filters'] : [];
        $topType = isset($finderParams['filters']['type']) ? $finderParams['filters']['type'] : 'top_users_connections';
        unset($finderParams['filters']['type']);
        $organizations = isset($finderParams['hiddenFilters']['organization']) ?
            $finderParams['hiddenFilters']['organization'] :
            null;
        $finderParams['limit'] = isset($finderParams['limit']) ? intval($finderParams['limit']) : 10;
        switch ($topType) {
            case 'top_extension':
                $listData = $this->resourceRepo->findMimeTypesWithMostResources($finderParams['limit'], $organizations);
                break;
            case 'top_workspaces_resources':
                $listData = $this->workspaceManager->getWorkspacesWithMostResources($finderParams['limit'], $organizations);
                break;
            case 'top_workspaces_connections':
                $finderParams['filters']['action'] = LogWorkspaceToolReadEvent::ACTION;
                $listData = $this->topWorkspaceByAction($finderParams);
                break;
            case 'top_resources_views':
                $finderParams['filters']['action'] = LogResourceReadEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams);
                break;
            case 'top_resources_downloads':
                $finderParams['filters']['action'] = LogResourceExportEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams);
                break;
            case 'top_users_workspaces_enrolled':
                $listData = $this->userManager->getUsersEnrolledInMostWorkspaces($finderParams['limit'], $organizations);
                break;
            case 'top_users_workspaces_owners':
                $listData = $this->userManager->getUsersOwnersOfMostWorkspaces($finderParams['limit'], $organizations);
                break;
            case 'top_media_views':
                $finderParams['filters']['action'] = LogResourceReadEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams, true);
                break;
            case 'top_users_connections':
            default:
                $finderParams['filters']['action'] = LogUserLoginEvent::ACTION;
                $finderParams['sortBy'] = '-actions';
                $listData = $this->logManager->getUserActionsList($finderParams);
                $listData = $listData['data'];
                break;
        }

        foreach ($listData as $idx => &$data) {
            if (!isset($data['id'])) {
                $data['id'] = "top-{$idx}";
            }
        }

        return [
            'data' => $listData,
            'filters' => [
                ['property' => 'type', 'value' => $topType],
            ],
            'page' => 0,
            'pageSize' => $finderParams['limit'],
            'sortBy' => null,
            'totalResults' => count($listData),
        ];
    }
}
