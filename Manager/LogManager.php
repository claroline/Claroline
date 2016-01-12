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

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Log\LogWidgetConfig;

/**
 * @DI\Service("claroline.log.manager")
 */
class LogManager
{
    const LOG_PER_PAGE = 40;

    private $container;

    private $em;

    /** @var \CLaroline\CoreBundle\Repository\Log\LogRepository $logRepository */
    private $logRepository;

    /**
     * @DI\InjectParams({
     *     "container"          = @DI\Inject("service_container"),
     *     "entityManager"      = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param $container
     * @param $entityManager
     */
    public function __construct($container, $entityManager)
    {
        $this->container = $container;
        $this->em = $entityManager;
        $this->logRepository = $entityManager->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function getDesktopWidgetList(WidgetInstance $instance)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();


        $hiddenConfigs = $this->em
            ->getRepository('ClarolineCoreBundle:Log\LogHiddenWorkspaceWidgetConfig')
            ->findBy(array('user' => $user));

        $workspaceIds = array();

        foreach ($hiddenConfigs as $hiddenConfig) {
            $workspaceIds[] = $hiddenConfig->getWorkspaceId();
        }

        // Get manager and collaborator workspaces config
        /** @var \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces */
        $workspaces = $this->em
            ->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->findByUserAndRoleNamesNotIn($user, array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'), $workspaceIds);

        $configs = array();

        if (count($workspaces) > 0) {
            //add this method to the repository @see ligne 68
            $configs = $this->em
                ->getRepository('ClarolineCoreBundle:Log\LogWidgetConfig')->findByWorkspaces($workspaces);
        }

        $defaultInstance = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\WidgetInstance')
            ->findOneBy(
            array(
                'widget' => $instance->getWidget(),
                'isAdmin' => true,
                'workspace' => null,
                'user' => null,
                'isDesktop' => false
            )
        );

        $defaultConfig = $this->getLogConfig($defaultInstance);

        if ($defaultConfig === null) {
            $defaultConfig = new LogWidgetConfig();
            $defaultConfig->setRestrictions(
                $this->getDefaultWorkspaceConfigRestrictions()
            );
            $defaultConfig->setAmount(5);
        }

        // Complete missing configs
        foreach ($workspaces as $workspace) {
            $config = null;

            for ($i = 0, $countConfigs = count($configs); $i < $countConfigs && $config === null; ++$i) {
                $current = $configs[$i];
                if ($current->getWidgetInstance()->getWorkspace()->getId() == $workspace->getId()) {
                    $config = $current;
                }
            }

            if ($config === null) {
                $config = new LogWidgetConfig();
                $config->copy($defaultConfig);
                $widgetInstance = new WidgetInstance();
                $widgetInstance->setWorkspace($workspace);
                $config->setWidgetInstance($widgetInstance);
                $configs[] = $config;
            }
        }

        // Remove configs which hasAllRestriction
        $configsCleaned = array();
        $events = $this->container->get('claroline.event.manager')
            ->getEvents(LogGenericEvent::DISPLAYED_WORKSPACE);

        foreach ($configs as $config) {
            if ($config->hasAllRestriction($events) === false) {
                $configsCleaned[] = $config;
            }
        }

        $configs = $configsCleaned;

        if (count($configs) === 0) {
            return null;
        }

        $desktopConfig = $this->getLogConfig($instance);
        $desktopConfig = $desktopConfig === null ? $defaultConfig: $desktopConfig;
        $query = $this->logRepository->findLogsThroughConfigs($configs, $desktopConfig->getAmount());
        $logs = $query->getResult();
        $chartData = $this->logRepository->countByDayThroughConfigs($configs, $this->getDefaultRange());

        //List item delegation
        $views = $this->renderLogs($logs);

        return array(
            'logs' => $logs,
            'listItemViews' => $views,
            'chartData' => $chartData,
            'logAmount' => $desktopConfig->getAmount(),
            'isDesktop' => true,
            'title' => $this->container->get('translator')->trans(
                'your_workspace_activity_overview',
                array(),
                'platform'
            )
        );
    }

    public function getWorkspaceWidgetList(WidgetInstance $instance)
    {
        $workspace = $instance->getWorkspace();

        if (!$this->isAllowedToViewLogs($workspace)) {
            return null;
        }

        $config = $this->getLogConfig($instance);

        if ($config === null) {
            $defaultConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\WidgetInstance')
                ->findOneBy(array('isDesktop' => false, 'isAdmin' => true));

            $config = new LogWidgetConfig();

            if ($defaultConfig !== null) {
                $config->copy($this->getLogConfig($defaultConfig));
            }

            $config->setRestrictions(
                $this->container->get('claroline.log.manager')->getDefaultWorkspaceConfigRestrictions()
            );
            $widgetInstance = new WidgetInstance();
            $widgetInstance->setWorkspace($workspace);
            $config->setWidgetInstance($widgetInstance);
        }

        /** @var \Claroline\CoreBundle\Manager\EventManager $eventManager */
        $eventManager = $this->container->get('claroline.event.manager');

        if ($config->hasNoRestriction()) {
            return null;
        }

        $query = $this->logRepository->findLogsThroughConfigs(array($config), $config->getAmount());
        $logs = $query->getResult();
        $chartData = $this->logRepository->countByDayThroughConfigs(array($config), $this->getDefaultRange());

        //List item delegation
        $views = $this->renderLogs($logs);

        $workspaceEvents = $eventManager->getEvents(LogGenericEvent::DISPLAYED_WORKSPACE);

        if ($config->hasAllRestriction(count($workspaceEvents))) {
            $title = $this->container->get('translator')->trans(
                'recent_all_workspace_activities_overview',
                array('%workspaceName%' => $workspace->getName()),
                'platform'
            );
        } else {
            $title = $this->container->get('translator')->trans(
                'Overview of recent activities in %workspaceName%',
                array('%workspaceName%' => $workspace->getName()),
                'platform'
            );
        }

        return array(
            'logs' => $logs,
            'listItemViews' => $views,
            'chartData'     => $chartData,
            'workspace'     => $workspace,
            'logAmount'     => $config->getAmount(),
            'title'         => $title,
            'isDesktop'     => false
        );
    }

    public function getAdminList($page, $maxResult = -1)
    {
        return $this->getList(
            $page,
            'admin',
            $this->container->get('claroline.form.adminLogFilter'),
            null,
            $maxResult
        );
    }

    public function getWorkspaceList($workspace, $page, $maxResult = -1)
    {
        if ($workspace == null) {
            $workspaceIds = $this->getAdminOrCollaboratorWorkspaceIds();
        } else {
            $workspaceIds = array($workspace->getId());
        }

        $params = $this->getList(
            $page,
            'workspace',
            $this->container->get('claroline.form.workspaceLogFilter'),
            $workspaceIds,
            $maxResult,
            null,
            null
        );
        $params['workspace'] = $workspace;

        return $params;
    }

    public function getResourceList($resource, $page, $maxResult = -1)
    {
        $resourceNodeIds = array($resource->getResourceNode()->getId());

        $params = $this->getList(
            $page,
            'workspace',
            $this->container->get('claroline.form.resourceLogFilter'),
            null,
            $maxResult,
            $resourceNodeIds,
            get_class($resource)
        );
        $params['_resource'] = $resource;

        return $params;
    }

    public function getList(
        $page,
        $actionsRestriction,
        $logFilterFormType,
        $workspaceIds = null,
        $maxResult = -1,
        $resourceNodeIds = null,
        $resourceClass = null
    )
    {
        $dateRangeToTextTransformer = new DateRangeToTextTransformer($this->container->get('translator'));
        $data = $this->processFormData(
            $actionsRestriction,
            $logFilterFormType,
            $workspaceIds,
            $resourceClass,
            $dateRangeToTextTransformer
        );
        $range = $data['range'];

        $filterForm = $this->container->get('form.factory')->create($logFilterFormType, $data);

        $data['range'] = $dateRangeToTextTransformer->transform($range);
        $filter = urlencode(json_encode($data));

        //Find if action refers to an resource type
        $actionData = $this->getResourceTypeFromAction($data['action']);
        $actionString = $actionData['action'];
        $resourceType = $actionData['resourceType'];

        $query = $this->logRepository->findFilteredLogsQuery(
            $actionString,
            $range,
            $data['user'],
            $actionsRestriction,
            $workspaceIds,
            $maxResult,
            $resourceType,
            $resourceNodeIds
        );

        $adapter = new DoctrineORMAdapter($query);
        $pager   = new PagerFanta($adapter);
        $pager->setMaxPerPage(self::LOG_PER_PAGE);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $chartData = $this->logRepository->countByDayFilteredLogs(
            $actionString,
            $range,
            $data['user'],
            $actionsRestriction,
            $workspaceIds,
            false,
            $resourceType,
            $resourceNodeIds
        );

        //List item delegation
        $views = $this->renderLogs($pager->getCurrentPageResults());
        //$views = array();
        return array(
            'pager' => $pager,
            'listItemViews' => $views,
            'filter' => $filter,
            'filterForm' => $filterForm->createView(),
            'chartData' => $chartData,
            'actionName' => $actionString
        );
    }

    public function countByUserWorkspaceList($workspace, $page)
    {
        if ($workspace == null) {
            $workspaceIds = $this->getAdminOrCollaboratorWorkspaceIds();
        } else {
            $workspaceIds = array($workspace->getId());
        }

        $params = $this->countByUser(
            $page,
            'workspace',
            $this->container->get('claroline.form.workspaceLogFilter'),
            $workspaceIds,
            null,
            null
        );
        $params['workspace'] = $workspace;

        return $params;
    }

    public function countByUserResourceList($resource, $page)
    {
        $resourceNodeIds = array($resource->getResourceNode()->getId());

        $params = $this->countByUser(
            $page,
            'workspace',
            $this->container->get('claroline.form.resourceLogFilter'),
            null,
            $resourceNodeIds,
            get_class($resource)
        );
        $params['_resource'] = $resource;

        return $params;
    }

    public function countByUser(
        $page,
        $actionsRestriction,
        $logFilterFormType,
        $workspaceIds = null,
        $resourceNodeIds = null,
        $resourceClass = null
    )
    {
        $page = max(1, $page);
        $dateRangeToTextTransformer = new DateRangeToTextTransformer($this->container->get('translator'));
        $data = $this->processFormData(
            $actionsRestriction,
            $logFilterFormType,
            $workspaceIds,
            $resourceClass,
            $dateRangeToTextTransformer
        );
        $range = $data['range'];
        $orderBy = 'name';
        if (isset($data['orderBy'])) {
            $orderBy = $data['orderBy'];
        }
        $order = 'ASC';
        if (isset($data['order'])) {
            $order = $data['order'];
        }

        $filterForm = $this->container->get('form.factory')->create($logFilterFormType, $data);

        $data['range'] = $dateRangeToTextTransformer->transform($range);
        $filter = urlencode(json_encode($data));

        //Find if action refers to an resource type
        $actionData = $this->getResourceTypeFromAction($data['action']);
        $actionString = $actionData['action'];
        $resourceType = $actionData['resourceType'];

        $nbUsers = $this->logRepository->countTopUsersByAction(
            $actionString,
            $range,
            $data['user'],
            $actionsRestriction,
            $workspaceIds,
            $resourceType,
            $resourceNodeIds,
            false
        );

        $maxResult = self::LOG_PER_PAGE;
        if (($page-1)*$maxResult>$nbUsers) {
            throw new NotFoundHttpException();
        }

        $topUsers = $this->logRepository->topUsersByActionQuery(
            $actionString,
            $range,
            $data['user'],
            $actionsRestriction,
            $workspaceIds,
            $maxResult,
            $resourceType,
            $resourceNodeIds,
            false,
            $page,
            $orderBy,
            $order
        )->getResult();


        $formatedData = $this->formatTopUserDataArray($topUsers);
        $resultUserList = null;
        if (!empty($formatedData)) {
            $userActionsByDay = $this->logRepository->findUserActionsByDay(
                $actionString,
                $range,
                $actionsRestriction,
                $workspaceIds,
                $resourceType,
                $resourceNodeIds,
                $formatedData['ids']
            );
            $userActionsByDay[] = null;
            $resultUserList = $formatedData['userData'];
            $currentUserId = null;
            $userActionsArray = array();
            foreach ($userActionsByDay as $userAction) {
                if ($userAction === null || ($currentUserId !== null && $currentUserId != $userAction['id'])) {
                    $resultUserList[$currentUserId]['stats'] = $this
                        ->logRepository
                        ->extractChartData($userActionsArray, $range);
                    $resultUserList[$currentUserId]['maxValue'] = max(array_column($userActionsArray, 'total'));
                    $userActionsArray = array();
                }
                if ($userAction !== null) {
                    $currentUserId = $userAction['id'];
                    $userActionsArray[] = $userAction;
                }
            }
        }
        $adapter = new FixedAdapter($nbUsers, $resultUserList);
        $pager   = new PagerFanta($adapter);
        $pager->setMaxPerPage(self::LOG_PER_PAGE);
        $pager->setCurrentPage($page);

        return array(
            'pager'         => $pager,
            'filter'        => $filter,
            'filterForm'    => $filterForm->createView(),
            'actionName'    => $actionString,
            'orderBy'       => $orderBy,
            'order'         => $order
        );
    }

    public function getWorkspaceVisibilityForDesktopWidget(User $user, array $workspaces)
    {
        $workspacesVisibility = array();

        foreach ($workspaces as $workspace) {
            $workspacesVisibility[$workspace->getId()] = true;
        }

        $hiddenWorkspaceConfigs = $this->em
            ->getRepository('ClarolineCoreBundle:Log\LogHiddenWorkspaceWidgetConfig')
            ->findBy(array('user' => $user));

        foreach ($hiddenWorkspaceConfigs as $config) {
            if ($workspacesVisibility[$config->getWorkspaceId()] !== null) {
                $workspacesVisibility[$config->getWorkspaceId()] = false;
            }
        }

        return $workspacesVisibility;
    }

    /**
     * @param null $domain
     *
     * @return array
     */
    public function getDefaultConfigRestrictions($domain = null)
    {
        return $this->container->get('claroline.event.manager')->getEvents($domain);
    }

    /**
     * @return array
     */
    public function getDefaultWorkspaceConfigRestrictions()
    {
        return $this->getDefaultConfigRestrictions(LogGenericEvent::DISPLAYED_WORKSPACE);
    }

    public function getLogConfig(WidgetInstance $config = null)
    {
        return $this->em
            ->getRepository('ClarolineCoreBundle:Log\LogWidgetConfig')
            ->findOneBy(array('widgetInstance' => $config));
    }

    protected function getResourceTypeFromAction($action)
    {
        //Find if action refers to an resource type
        $actionString = $action;
        $resourceType = null;
        preg_match('/\[\[([^\]]+)\]\]/', $action, $matches);
        if (!empty($matches)) {
            $resourceType = $matches[1];
            $actionString = preg_replace('/\[\[([^\]]+)\]\]/', '', $action);
            $actionString = trim($actionString);
        }

        return array('action'=>$actionString, 'resourceType'=>$resourceType);
    }

    protected function processFormData(
        $actionsRestriction,
        $logFilterFormType,
        $workspaceIds,
        $resourceClass,
        $dateRangeToTextTransformer
    )
    {
        $request = $this->container->get('request');
        $data = $request->query->all();

        $action = null;
        $range = null;
        $userSearch = null;
        if (!empty($data['orderBy'])) {
            $orderBy = $data['orderBy'];
            $order = $data['order'];
        }

        if (array_key_exists('filter', $data)) {
            $decodeFilter = json_decode(urldecode($data['filter']));
            if ($decodeFilter !== null) {
                $action = $decodeFilter->action;
                $range = $dateRangeToTextTransformer->reverseTransform($decodeFilter->range);
                $userSearch = $decodeFilter->user;
            }
        } else {
            $dataClass['resourceClass'] = $resourceClass ? $resourceClass: null;
            $tmpForm = $this->container->get('form.factory')->create($logFilterFormType, $dataClass);
            $tmpForm->submit($request);
            $formData = $tmpForm->getData();
            $action = isset($formData['action']) ? $formData['action']: null;
            $range = isset($formData['range']) ?$formData['range']:null;
            $userSearch = isset($formData['user'])?$formData['user']:null;
        }

        if ($range == null) {
            $range = $this->getDefaultRange();
        }

        if ($action == null && $actionsRestriction == "workspace" && $workspaceIds !== null) {
            $action = LogWorkspaceEnterEvent::ACTION;
        }

        $data = array();
        $data['action'] = $action;
        $data['range'] = $range;
        $data['user'] = $userSearch;

        if (isset($orderBy) && isset($order)) {
            $data['orderBy'] = $orderBy;
            $data['order'] = $order;
        }

        if ($resourceClass !== null) {
            $data['resourceClass'] = $resourceClass;
        }

        return $data;
    }

    protected function isAllowedToViewLogs($workspace)
    {
        $security = $this->container->get('security.authorization_checker');

        return $security->isGranted('ROLE_WS_COLLABORATOR_' . $workspace->getGuid())
            || $security->isGranted('ROLE_WS_MANAGER_'.$workspace->getGuid());
    }

    protected function renderLogs($logs)
    {
        //List item delegation
        $views = array();
        foreach ($logs as $log) {
            $eventName = 'create_log_list_item_'.$log->getAction();
            $event     = new LogCreateDelegateViewEvent($log);

            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get('event_dispatcher');

            if ($eventDispatcher->hasListeners($eventName)) {
                $event = $eventDispatcher->dispatch($eventName, $event);

                $views[$log->getId().''] = $event->getResponseContent();
            }
        }

        return $views;
    }

    protected function getDefaultRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P29D')); // P29D means a period of 29 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    protected function getYesterdayRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);
        $endDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    protected function getAdminOrCollaboratorWorkspaceIds()
    {
        $workspaceIds = array();
        $loggedUser = $this->container->get('security.token_storage')->getToken()->getUser();
        $workspaceIdsResult = $this->em
            ->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->findIdsByUserAndRoleNames($loggedUser, array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'));

        foreach ($workspaceIdsResult as $line) {
            $workspaceIds[] = $line['id'];
        }

        return $workspaceIds;
    }

    protected function formatTopUserDataArray($topUsers)
    {
        if ($topUsers === null || count($topUsers) == 0) {
            return array();
        }

        $topUsersFormatedArray = array();
        $topUsersIdList = array();
        foreach ($topUsers as $topUser) {
            $id = $topUser['id'];
            $topUser['stats'] = array();
            $topUsersFormatedArray[$id] = $topUser;
            $topUsersIdList[] = $id;
        }

        return array('ids' => $topUsersIdList, 'userData' => $topUsersFormatedArray);
    }

}
