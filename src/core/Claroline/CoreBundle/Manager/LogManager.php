<?php
namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use Claroline\CoreBundle\Form\WorkspaceLogFilterType;
use Claroline\CoreBundle\Form\AdminLogFilterType;
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
        /** @var \CLaroline\CoreBundle\Repository\Log\LogRepository $logRepository */
        $logRepository = $entitymanager->getRepository('ClarolineCoreBundle:Log\Log');

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
            $defaultConfig->setWorkspace(null);
            $defaultConfig->setIsDefault(true);
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
            if ($config->hasAllRestriction() === false) {
                $configsCleaned[] = $config;
            }
        }

        $configs = $configsCleaned;

        if (count($configs) === 0) {
            return null;
        }

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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Log\Log');

        $config = $this->getWorkspaceWidgetConfig($workspace);
        if ($config === null) {
            $defaultConfig = $this->getDefaultWorkspaceWidgetConfig();

            $config = new LogWorkspaceWidgetConfig();
            if ($defaultConfig !== null) {
                $config->copy($defaultConfig);
            }
            $config->setWorkspace($workspace);
        }

        if ($config->hasAllRestriction()) {
            return null;
        }
        $query = $repository->findLogsThroughConfigs(array($config), $config->getAmount());
        $logs = $query->getResult();
        $chartData = $repository->countByDayThroughConfigs(array($config), $this->getDefaultRange());

        //List item delegation
        $views = $this->renderLogs($logs);

        if ($config->hasNoRestriction()) {
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
            new AdminLogFilterType(),
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
            new WorkspaceLogFilterType(),
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

        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var \Claroline\CoreBundle\Repository\Log\LogRepository $repository */
        $repository = $em->getRepository('ClarolineCoreBundle:Log\Log');

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

    public function getWorkspaceVisibilityForDesktopWidget($user, $workspaces)
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
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em
            ->getRepository('ClarolineCoreBundle:Log\LogDesktopWidgetConfig')
            ->findOneBy(array('user' => $user, 'isDefault' => false));
    }

    public function getDefaultDesktopWidgetConfig()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em
            ->getRepository('ClarolineCoreBundle:Log\LogDesktopWidgetConfig')
            ->findOneBy(array('user' => null, 'isDefault' => true));
    }

    public function getWorkspaceWidgetConfig($workspace)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em
            ->getRepository('ClarolineCoreBundle:Log\LogWorkspaceWidgetConfig')
            ->findOneBy(array('workspace' => $workspace, 'isDefault' => false));
    }

    public function getDefaultWorkspaceWidgetConfig()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em
            ->getRepository('ClarolineCoreBundle:Log\LogWorkspaceWidgetConfig')
            ->findOneBy(array('workspace' => null, 'isDefault' => true));
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

    /**
     * Get all existing event name with their associated label
     *
     * @return array
     */
    public function getEventList()
    {
        /** @var \AppKernel $kernel */
        $kernel             = $this->container->get('kernel');
        $suffixLogPath      = '/Event/Log';
        $suffixLogNamespace = '\Event\Log';
        $bundles            = $kernel->getBundles();
        $eventList          = array();

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        foreach ($bundles as $bundle) {
            $bundleEventLogDirectory = $bundle->getPath() .  $suffixLogPath;
            if (file_exists($bundleEventLogDirectory)) {
                $finder = new Finder();
                $finder->files()->in($bundleEventLogDirectory)->sortByName();
                /** @var \Symfony\Component\Finder\SplFileInfo $file */
                foreach ($finder as $file) {
                    $classNamespace = $bundle->getNamespace() . $suffixLogNamespace . '\\' . $file->getBasename('.' . $file->getExtension());
                    if (in_array('Claroline\CoreBundle\Event\Log\LogGenericEvent', class_parents($classNamespace))) {
                        $reflectionClass = new \ReflectionClass($classNamespace);
                        $classConstants = $reflectionClass->getConstants();
                        foreach ($classConstants as $key => $classConstant) {
                            if (preg_match('/^ACTION/', $key)) {
                                $eventList[$classConstant] = $translator->trans(sprintf('log_%s_title', $classConstant), array(), 'log');
                            }
                        }
                    }
                }
            }
        }

        return $eventList;
    }
}
