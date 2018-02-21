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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.widget_manager")
 */
class WidgetManager
{
    use LoggableTrait;

    private $om;
    private $widgetDisplayConfigRepo;
    private $widgetInstanceRepo;
    private $widgetRepo;
    private $router;
    private $translator;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator"),
     *     "container"  = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        RouterInterface $router,
        TranslatorInterface $translator,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->widgetDisplayConfigRepo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetDisplayConfig');
        $this->widgetInstanceRepo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetInstance');
        $this->widgetRepo = $om->getRepository('ClarolineCoreBundle:Widget\Widget');
        $this->router = $router;
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * Creates a widget instance.
     *
     * @param \Claroline\CoreBundle\Entity\Widget\Widget       $widget
     * @param bool                                             $isAdmin
     * @param bool                                             $isDesktop
     * @param \Claroline\CoreBundle\Entity\User                $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $ws
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     *
     * @throws \Exception
     */
    public function createInstance(
        Widget $widget,
        $isAdmin,
        $isDesktop,
        User $user = null,
        Workspace $ws = null
    ) {
        if (!$widget->isDisplayableInDesktop()) {
            if ($isDesktop || $user) {
                throw new \Exception("This widget doesn't support the desktop");
            }
        }

        if (!$widget->isDisplayableInWorkspace()) {
            if (!$isDesktop || $ws) {
                throw new \Exception("This widget doesn't support the workspace");
            }
        }

        $instance = new WidgetInstance($widget);
        $instance->setName($this->translator->trans($widget->getName(), [], 'widget'));
        $instance->setIsAdmin($isAdmin);
        $instance->setIsDesktop($isDesktop);
        $instance->setWidget($widget);
        $instance->setUser($user);
        $instance->setWorkspace($ws);
        $this->om->persist($instance);
        $this->om->flush();

        return $instance;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     */
    public function removeInstance(WidgetInstance $widgetInstance)
    {
        $this->om->remove($widgetInstance);
        $this->om->flush();
    }

    public function persistWidget(Widget $widget)
    {
        $this->om->persist($widget);
        $this->om->flush();
    }

    /**
     * Finds all widgets.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getAll()
    {
        return  $this->widgetRepo->findAll();
    }

    /**
     * Finds all widgets displayable in the desktop.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getDesktopWidgets()
    {
        return $this->widgetRepo->findBy(['isDisplayableInDesktop' => true]);
    }

    /**
     * Finds all widgets displayable in a workspace.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getWorkspaceWidgets()
    {
        return $this->widgetRepo->findBy(['isDisplayableInWorkspace' => true]);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getDesktopInstances(User $user)
    {
        return  $this->widgetInstanceRepo->findBy(['user' => $user]);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getWorkspaceInstances(Workspace $workspace)
    {
        return  $this->widgetInstanceRepo->findBy(['workspace' => $workspace]);
    }

    /**
     * @todo define what I do
     *
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getAdminDesktopWidgetInstance(array $excludedWidgetInstances)
    {
        if (0 === count($excludedWidgetInstances)) {
            return $this->widgetInstanceRepo->findBy(
                [
                    'isAdmin' => true,
                    'isDesktop' => true,
                ]
            );
        }

        return $this->widgetInstanceRepo
            ->findAdminDesktopWidgetInstance($excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getAdminWorkspaceWidgetInstance(array $excludedWidgetInstances)
    {
        if (0 === count($excludedWidgetInstances)) {
            return $this->widgetInstanceRepo->findBy(
                [
                    'isAdmin' => true,
                    'isDesktop' => false,
                ]
            );
        }

        return $this->widgetInstanceRepo
            ->findAdminWorkspaceWidgetInstance($excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param array                             $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getDesktopWidgetInstance(
        User $user,
        array $excludedWidgetInstances
    ) {
        if (0 === count($excludedWidgetInstances)) {
            return $this->widgetInstanceRepo->findBy(
                [
                    'user' => $user,
                    'isAdmin' => false,
                    'isDesktop' => true,
                ]
            );
        }

        return $this->widgetInstanceRepo
            ->findDesktopWidgetInstance($user, $excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param array                                            $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getWorkspaceWidgetInstance(Workspace $workspace, array $excludedWidgetInstances)
    {
        if (0 === count($excludedWidgetInstances)) {
            return $this->widgetInstanceRepo->findBy(
                [
                    'workspace' => $workspace,
                    'isAdmin' => false,
                    'isDesktop' => false,
                ]
            );
        }

        return $this->widgetInstanceRepo->findWorkspaceWidgetInstance($workspace, $excludedWidgetInstances);
    }

    public function getAdminWidgetDisplayConfigsByWHTCs(array $widgetHTCs)
    {
        $results = [];
        $widgetInstances = [];

        foreach ($widgetHTCs as $whtc) {
            $widgetInstance = $whtc->getWidgetInstance();
            $widgetInstances[] = $widgetInstance;
        }
        $adminWDCs = $this->getAdminWidgetDisplayConfigsByWidgets($widgetInstances);

        foreach ($adminWDCs as $wdc) {
            $widgetInstance = $wdc->getWidgetInstance();
            $id = $widgetInstance->getId();
            $results[$id] = $wdc;
        }

        return $results;
    }

    public function generateWidgetDisplayConfigsForUser(User $user, array $widgetHTCs)
    {
        $results = [];
        $widgetInstances = [];
        $mappedWHTCs = [];
        $userTab = [];
        $adminTab = [];

        foreach ($widgetHTCs as $whtc) {
            $widgetInstance = $whtc->getWidgetInstance();
            $widgetInstances[] = $widgetInstance;

            if ('admin' === $whtc->getType()) {
                $mappedWHTCs[$widgetInstance->getId()] = $whtc;
            }
        }
        $usersWDCs = $this->getWidgetDisplayConfigsByUserAndWidgets($user, $widgetInstances);
        $adminWDCs = $this->getAdminWidgetDisplayConfigsByWidgets($widgetInstances);

        foreach ($usersWDCs as $userWDC) {
            $widgetInstanceId = $userWDC->getWidgetInstance()->getId();
            $userTab[$widgetInstanceId] = $userWDC;
        }

        foreach ($adminWDCs as $adminWDC) {
            $widgetInstanceId = $adminWDC->getWidgetInstance()->getId();
            $adminTab[$widgetInstanceId] = $adminWDC;
        }

        $this->om->startFlushSuite();

        foreach ($widgetInstances as $widgetInstance) {
            $id = $widgetInstance->getId();

            if (isset($userTab[$id])) {
                if (isset($mappedWHTCs[$id]) && isset($adminTab[$id])) {
                    $changed = false;

                    if ($userTab[$id]->getColor() !== $adminTab[$id]->getColor() ||
                        $userTab[$id]->getDetails() !== $adminTab[$id]->getDetails()) {
                        $userTab[$id]->setColor($adminTab[$id]->getColor());
                        $userTab[$id]->setDetails($adminTab[$id]->getDetails());
                        $changed = true;
                    }

                    if ($changed) {
                        $this->om->persist($userTab[$id]);
                    }
                }
                $results[$id] = $userTab[$id];
            } elseif (isset($adminTab[$id])) {
                $wdc = new WidgetDisplayConfig();
                $wdc->setWidgetInstance($widgetInstance);
                $wdc->setUser($user);
                $wdc->setRow($adminTab[$id]->getRow());
                $wdc->setColumn($adminTab[$id]->getColumn());
                $wdc->setWidth($adminTab[$id]->getWidth());
                $wdc->setHeight($adminTab[$id]->getHeight());
                $wdc->setColor($adminTab[$id]->getColor());
                $this->om->persist($wdc);
                $results[$id] = $wdc;
            } else {
                $widget = $widgetInstance->getWidget();
                $wdc = new WidgetDisplayConfig();
                $wdc->setWidgetInstance($widgetInstance);
                $wdc->setUser($user);
                $wdc->setWidth($widget->getDefaultWidth());
                $wdc->setHeight($widget->getDefaultHeight());
                $this->om->persist($wdc);
                $results[$id] = $wdc;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    public function generateWidgetDisplayConfigsForWorkspace(
        Workspace $workspace,
        array $widgetHTCs
    ) {
        $results = [];
        $widgetInstances = [];
        $workspaceTab = [];

        foreach ($widgetHTCs as $htc) {
            $widgetInstances[] = $htc->getWidgetInstance();
        }
        $workspaceWDCs = $this->getWidgetDisplayConfigsByWorkspaceAndWidgets(
            $workspace,
            $widgetInstances
        );

        foreach ($workspaceWDCs as $wdc) {
            $widgetInstanceId = $wdc->getWidgetInstance()->getId();
            $workspaceTab[$widgetInstanceId] = $wdc;
        }

        $this->om->startFlushSuite();

        foreach ($widgetInstances as $widgetInstance) {
            $id = $widgetInstance->getId();

            if (isset($workspaceTab[$id])) {
                $results[$id] = $workspaceTab[$id];
            } else {
                $widget = $widgetInstance->getWidget();
                $wdc = new WidgetDisplayConfig();
                $wdc->setWidgetInstance($widgetInstance);
                $wdc->setWorkspace($workspace);
                $wdc->setWidth($widget->getDefaultWidth());
                $wdc->setHeight($widget->getDefaultHeight());
                $this->om->persist($wdc);
                $results[$id] = $wdc;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    public function generateWidgetDisplayConfigsForAdmin(array $widgetHTCs)
    {
        $results = [];
        $widgetInstances = [];
        $adminTab = [];

        foreach ($widgetHTCs as $htc) {
            $widgetInstances[] = $htc->getWidgetInstance();
        }
        $adminWDCs = $this->getWidgetDisplayConfigsByWidgetsForAdmin($widgetInstances);

        foreach ($adminWDCs as $wdc) {
            $widgetInstanceId = $wdc->getWidgetInstance()->getId();

            $adminTab[$widgetInstanceId] = $wdc;
        }

        $this->om->startFlushSuite();

        foreach ($widgetInstances as $widgetInstance) {
            $id = $widgetInstance->getId();

            if (isset($adminTab[$id])) {
                $results[$id] = $adminTab[$id];
            } else {
                $widget = $widgetInstance->getWidget();
                $wdc = new WidgetDisplayConfig();
                $wdc->setWidgetInstance($widgetInstance);
                $wdc->setWidth($widget->getDefaultWidth());
                $wdc->setHeight($widget->getDefaultHeight());
                $this->om->persist($wdc);
                $results[$id] = $wdc;
            }
        }
        $this->om->endFlushSuite();

        return $results;
    }

    public function persistWidgetDisplayConfigs(array $configs)
    {
        $this->om->startFlushSuite();

        foreach ($configs as $config) {
            $this->om->persist($config);
        }
        $this->om->endFlushSuite();
    }

    public function persistWidgetConfigs(
        WidgetInstance $widgetInstance = null,
        WidgetHomeTabConfig $widgetHomeTabConfig = null,
        WidgetDisplayConfig $widgetDisplayConfig = null
    ) {
        if ($widgetInstance) {
            $this->om->persist($widgetInstance);
        }

        if ($widgetHomeTabConfig) {
            $this->om->persist($widgetHomeTabConfig);
        }

        if ($widgetDisplayConfig) {
            $this->om->persist($widgetDisplayConfig);
        }
        $this->om->flush();
    }

    /***************************************************
     * Access to WidgetDisplayConfigRepository methods *
     ***************************************************/

    public function getWidgetDisplayConfigById($id)
    {
        return $this->widgetDisplayConfigRepo->findOneById($id);
    }

    public function getWidgetDisplayConfigsByUserAndWidgets(
        User $user,
        array $widgetInstances,
        $executeQuery = true
    ) {
        return count($widgetInstances) > 0 ?
            $this->widgetDisplayConfigRepo->findWidgetDisplayConfigsByUserAndWidgets(
                $user,
                $widgetInstances,
                $executeQuery
            ) :
            [];
    }

    public function getAdminWidgetDisplayConfigsByWidgets(
        array $widgetInstances,
        $executeQuery = true
    ) {
        return count($widgetInstances) > 0 ?
            $this->widgetDisplayConfigRepo->findAdminWidgetDisplayConfigsByWidgets(
                $widgetInstances,
                $executeQuery
            ) :
            [];
    }

    public function getWidgetDisplayConfigsByWorkspaceAndWidgets(
        Workspace $workspace,
        array $widgetInstances,
        $executeQuery = true
    ) {
        return count($widgetInstances) > 0 ?
            $this->widgetDisplayConfigRepo->findWidgetDisplayConfigsByWorkspaceAndWidgets(
                $workspace,
                $widgetInstances,
                $executeQuery
            ) :
            [];
    }

    public function getWidgetDisplayConfigsByWidgetsForAdmin(
        array $widgetInstances,
        $executeQuery = true
    ) {
        return count($widgetInstances) > 0 ?
            $this->widgetDisplayConfigRepo->findWidgetDisplayConfigsByWidgetsForAdmin(
                $widgetInstances,
                $executeQuery
            ) :
            [];
    }

    public function getWidgetDisplayConfigsByWorkspaceAndWidgetHTCs(
        Workspace $workspace,
        array $widgetHomeTabConfigs,
        $executeQuery = true
    ) {
        return count($widgetHomeTabConfigs) > 0 ?
            $this->widgetDisplayConfigRepo->findWidgetDisplayConfigsByWorkspaceAndWidgetHTCs(
                $workspace,
                $widgetHomeTabConfigs,
                $executeQuery
            ) :
            [];
    }

    public function importTextFromCsv($file)
    {
        $data = file_get_contents($file);
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $lines = str_getcsv($data, PHP_EOL);
        $textWidget = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName('simple_text');
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';');
            $code = $values[0];
            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);
            $name = $values[1];
            $title = $values[2];
            $width = isset($values[4]) ? $values[4] : 4;
            $height = isset($values[5]) ? $values[5] : 3;
            $tab = $this->om->getRepository('ClarolineCoreBundle:Home\HomeTab')->findOneBy(['workspace' => $workspace, 'name' => $name]);
            $widgetHomeTabConfig = $this->om->getRepository('ClarolineCoreBundle:Widget\WidgetHomeTabConfig')
                ->findByWorkspaceAndHomeTabAndWidgetInstanceName($workspace, $tab, $title);

            if (!$widgetHomeTabConfig) {
                $widgetInstance = $this->createWidgetInstance(
                    $title,
                    $textWidget,
                    $tab,
                    $workspace
                );
            } else {
                $this->log("Widget {$title} already exists in workspace {$code}: Updating...");
                $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();
            }

            $simpleTextConfig = $this->container->get('claroline.manager.simple_text_manager')->getTextConfig($widgetInstance);

            if (!$simpleTextConfig) {
                $simpleTextConfig = new SimpleTextConfig();
                $simpleTextConfig->setWidgetInstance($widgetInstance);
            }

            $widgetDisplayConfigs = $widgetInstance->getWidgetDisplayConfigs();
            $widgetDisplayConfig = $widgetDisplayConfigs[0];

            if (!$widgetDisplayConfig) {
                $widgetDisplayConfig = new WidgetDisplayConfig();
                $widgetDisplayConfig->setWidgetInstance($widgetInstance);
                $widgetDisplayConfig->setWorkspace($workspace);
                $widgetDisplayConfig->setWidth($width);
                $widgetDisplayConfig->setHeight($height);
                $this->om->persist($widgetDisplayConfig);
            }

            $widgetDisplayConfig->setHeight($height);
            $widgetDisplayConfig->setWidth($width);
            $this->om->persist($widgetDisplayConfig);
            $content = file_get_contents($values[3]);
            $simpleTextConfig->setContent($content);
            $this->om->persist($simpleTextConfig);

            ++$i;

            if (0 === $i % 100) {
                $this->om->forceFlush();
                $this->om->clear();
                $textWidget = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName('simple_text');
                $this->om->merge($textWidget);
            }
        }

        $this->om->endFlushSuite();
    }

    public function createWidgetInstance(
        $name,
        Widget $widget,
        HomeTab $homeTab,
        Workspace $workspace = null,
        $isAdmin = false,
        $isLocked = false
    ) {
        $this->log("Create widget {$name} in {$workspace->getCode()}");
        $widgetInstance = new WidgetInstance();
        $widgetHomeTabConfig = new WidgetHomeTabConfig();

        if ($workspace) {
            $type = 'workspace';
            $isDesktop = false;
        } else {
            $type = 'user';
            $isDesktop = true;
        }

        $widgetInstance->setWorkspace($workspace);
        $widgetInstance->setName($name);
        $widgetInstance->setIsAdmin($isAdmin);
        $widgetInstance->setIsDesktop($isDesktop);
        $widgetInstance->setWidget($widget);
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
        $widgetHomeTabConfig->setWorkspace($workspace);
        $widgetHomeTabConfig->setLocked($isLocked);
        $widgetHomeTabConfig->setWidgetOrder(1);
        $widgetHomeTabConfig->setType($type);
        $this->om->persist($widgetInstance);
        $this->om->persist($widgetHomeTabConfig);

        return $widgetInstance;
    }

    public function getConfiguration(WidgetInstance $instance)
    {
        return $this->widgetDisplayConfigRepo->findOneBy([
          'workspace' => $instance->getWorkspace(),
          'user' => $instance->getUser(),
          'widgetInstance' => $instance,
        ]);
    }

    public function persistConfiguration(WidgetDisplayConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return int
     */
    public function getNbWidgetInstances()
    {
        return $this->widgetInstanceRepo->countWidgetInstances();
    }

    /**
     * @return int
     */
    public function getNbWorkspaceWidgetInstances()
    {
        return $this->widgetInstanceRepo->countWidgetInstances('workspace');
    }

    /**
     * @return int
     */
    public function getNbDesktopWidgetInstances()
    {
        return $this->widgetInstanceRepo->countWidgetInstances('desktop');
    }
}
