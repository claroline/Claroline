<?php
namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use Claroline\CoreBundle\Form\Log\WorkspaceLogFilterType;
use Claroline\CoreBundle\Form\Log\AdminLogFilterType;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Entity\Log\LogWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Log\LogDesktopWidgetConfig;

/**
 * @DI\Service("claroline.log.manager")
 */
class LogManager
{
    const LOG_PER_PAGE = 40;

    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getDesktopWidgetList()
    {
        $loggedUser    = $this->container->get('security.context')->getToken()->getUser();
        $entitymanager = $this->container->get('doctrine.orm.entity_manager');

        $desktopConfig = $this->getDesktopWidgetConfig($loggedUser);
        if ($desktopConfig === null) {
            $desktopConfig = $this->getDefaultDesktopWidgetConfig();
            if ($desktopConfig === null) {
                $desktopConfig = new LogDesktopWidgetConfig();
            }
        }

        $hiddenConfigs = $entitymanager
            ->getRepository('ClarolineCoreBundle:Log\LogHiddenWorkspaceWidgetConfig')
            ->findBy(array('user' => $loggedUser));

        $workspaceIds = array();
        foreach ($hiddenConfigs as $hiddenConfig) {
            $workspaceIds[] = $hiddenConfig->getWorkspaceId();
        }

        // Get manager and collaborator workspaces config
        /** @var \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[] $workspaces */
        $workspaces = $entitymanager
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findByUserAndRoleNamesNotIn($loggedUser, array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'), $workspaceIds);

        if (count($workspaces) > 0) {
            $configs = $entitymanager
                ->getRepository('ClarolineCoreBundle:Log\LogWorkspaceWidgetConfig')
                ->findBy(array('workspace' => $workspaces, 'isDefault' => false));
        } else {
            $configs = array();
        }

        $defaultConfig = $this->getDefaultWorkspaceWidgetConfig();
        if ($defaultConfig === null) {
            $defaultConfig = new LogWorkspaceWidgetConfig();
            $defaultConfig
                ->setWorkspace(null)
                ->setIsDefault(true)
                ->setRestrictions($this->container->get('claroline.log.manager')->getDefaultWorkspaceConfigRestrictions());
        }

        // Complete missing configs
        foreach ($workspaces as $workspace) {
            $config = null;

            for ($i = 0, $countConfigs = count($configs); $i < $countConfigs && $config === null; ++$i) {
                $current = $configs[$i];
                if ($current->getWorkspace()->getId() == $workspace->getId()) {
                    $config = $current;
                }
            }

            if ($config === null) {
                $config = new LogWorkspaceWidgetConfig();
                $config->copy($defaultConfig);
                $config->setWorkspace($workspace);
                $configs[] = $config;
            }
        }

        // Remove configs which hasAllRestriction
        $configsCleaned = array();

        foreach ($configs as $config) {
            if ($config->hasAllRestriction($this->container->get('claroline.event.manager')->getEvents(LogGenericEvent::DISPLAYED_WORKSPACE)) === false) {
                $configsCleaned[] = $config;
            }
        }

        $configs = $configsCleaned;

        if (count($configs) === 0) {
            return null;
        }

        /** @var \CLaroline\CoreBundle\Repository\Log\LogRepository $logRepository */
        $logRepository = $entitymanager->getRepository('ClarolineCoreBundle:Log\Log');

        $query     = $logRepository->findLogsThroughConfigs($configs, $desktopConfig->getAmount());
        $logs      = $query->getResult();
        $chartData = $logRepository->countByDayThroughConfigs($configs, $this->getDefaultRange());

        //List item delegation
        $views = $this->renderLogs($logs);

        return array(
            'logs' => $logs,
            'listItemViews' => $views,
            'chartData' => $chartData,
            'logAmount' => $desktopConfig->getAmount(),
            'isDesktop' => true,
            'title' => $this->container->get('translator')->trans(
                'Overview of recent activities of your workspaces',
                array(),
                'platform'
            )
        );
    }

    public function getWorkspaceWidgetList($workspace)
    {
        if (!$this->isAllowedToViewLogs($workspace)) {
            return null;
        }
        $em         = $this->container->get('doctrine.orm.entity_manager');
        /** @var \Claroline\CoreBundle\Repository\Log\LogRepository $repository */
        $repository = $em->getRepository('ClarolineCoreBundle:Log\Log');

        $config = $this->getWorkspaceWidgetConfig($workspace);
        if ($config === null) {
            $defaultConfig = $this->getDefaultWorkspaceWidgetConfig();

            $config = new LogWorkspaceWidgetConfig();
            if ($defaultConfig !== null) {
                $config->copy($defaultConfig);
            }
            $config
                ->setWorkspace($workspace)
                ->setRestrictions($this->container->get('claroline.log.manager')->getDefaultWorkspaceConfigRestrictions());
        }

        /** @var \Claroline\CoreBundle\Manager\EventManager $eventManager */
        $eventManager = $this->container->get('claroline.event.manager');

        if ($config->hasNoRestriction()) {
            return null;
        }
        $query     = $repository->findLogsThroughConfigs(array($config), $config->getAmount());
        $logs      = $query->getResult();
        $chartData = $repository->countByDayThroughConfigs(array($config), $this->getDefaultRange());

        //List item delegation
        $views = $this->renderLogs($logs);

        $workspaceEvents = $eventManager->getEvents(LogGenericEvent::DISPLAYED_WORKSPACE);

        if ($config->hasAllRestriction(count($workspaceEvents))) {
            $title = $this->container->get('translator')->trans(
                'Overview of all recent activities in %workspaceName%',
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
            'chartData' => $chartData,
            'workspace' => $workspace,
            'logAmount' => $config->getAmount(),
            'title' => $title
        );
    }

    public function getAdminList($page, $maxResult = -1)
    {
        return $this->getList(
            $page,
            'admin',
            $this->container->get('claroline.form.adminLogFilter'),
            'admin_log_filter_form',
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
            'workspace_log_filter_form',
            $workspaceIds,
            $maxResult
        );
        $params['workspace'] = $workspace;

        return $params;
    }

    public function getList(
        $page,
        $actionsRestriction,
        $logFilterFormType,
        $queryParamName,
        $workspaceIds = null,
        $maxResult = -1
    )
    {
        $request = $this->container->get('request');
        $data = $request->query->all();

        $action = null;
        $range = null;
        $userSearch = null;
        $dateRangeToTextTransformer = new DateRangeToTextTransformer($this->container->get('translator'));

        if (array_key_exists($queryParamName, $data)) {
            $data = $data[$queryParamName];
            $action = $data['action'];
            $range = $dateRangeToTextTransformer->reverseTransform($data['range']);
            $userSearch = $data['user'];
        } elseif (array_key_exists('filter', $data)) {
            $decodeFilter = json_decode(urldecode($data['filter']));
            if ($decodeFilter !== null) {
                $action = $decodeFilter->action;
                $range = $dateRangeToTextTransformer->reverseTransform($decodeFilter->range);
                $userSearch = $decodeFilter->user;
            }
        }

        if ($range == null) {
            $range = $this->getDefaultRange();
        }

        $data = array();
        $data['action'] = $action;
        $data['range'] = $range;
        $data['user'] = $userSearch;

        $filterForm = $this->container->get('form.factory')->create($logFilterFormType);
        $filterForm->setData($data);

        $data['range'] = $dateRangeToTextTransformer->transform($range);
        $filter = urlencode(json_encode($data));

        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var \Claroline\CoreBundle\Repository\Log\LogRepository $repository */
        $repository = $entityManager->getRepository('ClarolineCoreBundle:Log\Log');

        $query = $repository->findFilteredLogsQuery(
            $action,
            $range,
            $userSearch,
            $actionsRestriction,
            $workspaceIds,
            $maxResult
        );

        $adapter = new DoctrineORMAdapter($query);
        $pager   = new PagerFanta($adapter);
        $pager->setMaxPerPage(self::LOG_PER_PAGE);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $chartData = $repository->countByDayFilteredLogs(
            $action,
            $range,
            $userSearch,
            $actionsRestriction,
            $workspaceIds
        );

        //List item delegation
        $views = $this->renderLogs($pager->getCurrentPageResults());
        //$views = array();
        return array(
            'pager' => $pager,
            'listItemViews' => $views,
            'filter' => $filter,
            'filterForm' => $filterForm->createView(),
            'chartData' => $chartData
        );
    }

    public function getWorkspaceVisibilityForDesktopWidget(User $user, array $workspaces)
    {
        $workspacesVisibility = array();

        $em = $this->container->get('doctrine.orm.entity_manager');
        foreach ($workspaces as $workspace) {
            $workspacesVisibility[$workspace->getId()] = true;
        }

        $hiddenWorkspaceConfigs = $em
            ->getRepository('ClarolineCoreBundle:Log\LogHiddenWorkspaceWidgetConfig')
            ->findBy(array('user' => $user));

        foreach ($hiddenWorkspaceConfigs as $config) {
            if ($workspacesVisibility[$config->getWorkspaceId()] !== null) {
                $workspacesVisibility[$config->getWorkspaceId()] = false;
            }
        }

        return $workspacesVisibility;
    }

    public function getDesktopWidgetConfig($user)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        return $entityManager
            ->getRepository('ClarolineCoreBundle:Log\LogDesktopWidgetConfig')
            ->findOneBy(array('user' => $user, 'isDefault' => false));
    }

    public function getDefaultDesktopWidgetConfig()
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        return $entityManager
            ->getRepository('ClarolineCoreBundle:Log\LogDesktopWidgetConfig')
            ->findOneBy(array('user' => null, 'isDefault' => true));
    }

    public function getWorkspaceWidgetConfig($workspace)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        return $entityManager
            ->getRepository('ClarolineCoreBundle:Log\LogWorkspaceWidgetConfig')
            ->findOneBy(array('workspace' => $workspace->getId(), 'isDefault' => false));
    }

    public function getDefaultWorkspaceWidgetConfig()
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        return $entityManager
            ->getRepository('ClarolineCoreBundle:Log\LogWorkspaceWidgetConfig')
            ->findOneBy(array('workspace' => null, 'isDefault' => true));
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

    protected function isAllowedToViewLogs($workspace)
    {
        $security = $this->container->get('security.context');

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
        $loggedUser = $this->container->get('security.context')->getToken()->getUser();
        $workspaceIdsResult = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findIdsByUserAndRoleNames($loggedUser, array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'));

        foreach ($workspaceIdsResult as $line) {
            $workspaceIds[] = $line['id'];
        }

        return $workspaceIds;
    }
}
