<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\ExportToolEvent;
use Claroline\CoreBundle\Event\ImportToolEvent;
use Claroline\CoreBundle\Event\ConfigureWorkspaceToolEvent;
use Claroline\CoreBundle\Event\ConfigureDesktopToolEvent;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class HomeListener
{
    private $workspaceManager;
    private $homeTabManager;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed"                 = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"         = @DI\Inject("templating"),
     *     "wm"                 = @DI\Inject("claroline.widget.manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "securityContext"    = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        $em,
        $ed,
        $templating,
        $wm,
        WorkspaceManager $workspaceManager,
        HomeTabManager $homeTabManager,
        SecurityContextInterface $securityContext
    )
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->wm = $wm;
        $this->workspaceManager = $workspaceManager;
        $this->homeTabManager = $homeTabManager;
        $this->securityContext = $securityContext;
    }

    /**
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopHome(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopHome());
    }

    /**
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceHome(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceHome($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("configure_workspace_tool_home")
     */
    public function onWorkspaceConfigure(ConfigureWorkspaceToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\home:configuration.html.twig',
            array('workspace' => $event->getWorkspace(), 'tool' => $event->getTool())
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("configure_desktop_tool_home")
     */
    public function onDesktopConfigure(ConfigureDesktopToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\home:configuration.html.twig',
            array('tool' => $event->getTool())
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("tool_home_from_template")
     *
     * @param ImportToolEvent $event
     */
    public function onImportHome(ImportToolEvent $event)
    {
        $config = $event->getConfig();

        if (isset($config['widget'])) {
            $unknownWidgets = array();
            foreach ($config['widget'] as $widgetConfig) {
                $widget = $this->em->getRepository('ClarolineCoreBundle:Widget\Widget')
                    ->findOneByName($widgetConfig['name']);

                if ($widget === null) {
                    $unknownWidgets[] = $widgetConfig['name'];
                }

                $parent = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
                    ->findOneBy(array('widget' => $widget, 'parent' => null, 'isDesktop' => false));
                $displayConfig = new DisplayConfig();
                $displayConfig->setParent($parent);
                $displayConfig->setVisible($widgetConfig['is_visible']);
                $displayConfig->setWidget($widget);
                $displayConfig->setDesktop(false);
                $displayConfig->isLocked(true);
                $displayConfig->setWorkspace($event->getWorkspace());

                if (isset($widgetConfig['config'])) {
                    $this->ed->dispatch(
                        "widget_{$widgetConfig['name']}_from_template",
                        'ImportWidgetConfig',
                        array($widgetConfig['config'], $event->getWorkspace())
                    );
                }

                $this->em->persist($displayConfig);
            }
        }

        if (count($unknownWidgets) > 0) {
            $content = "Widget(s) ";

            foreach ($unknownWidgets as $unknown) {
                $content .= "{$unknown}, ";
            }

            $content .= "were not found";

            throw new \Exception($content);
        }
    }

    /**
     * @DI\Observe("tool_home_to_template")
     *
     * @param ExportToolEvent $event
     */
    public function onExportHome(ExportToolEvent $event)
    {
        $home = array();
        $workspace = $event->getWorkspace();
        $configs = $this->wm->generateWorkspaceDisplayConfig($workspace->getId());

        foreach ($configs as $config) {
            $widgetArray = array();
            $widgetArray['name'] = $config->getWidget()->getName();
            $widgetArray['is_visible'] = $config->isVisible();
            if ($config->getWidget()->isExportable()) {
                $newEvent = $this->ed->dispatch(
                    "widget_{$config->getWidget()->getName()}_to_template",
                    'ExportWidgetConfig',
                    array($config->getWidget(), $workspace)
                );

                $widgetArray['config'] = $newEvent->getConfig();
            }

            $perms[] = $widgetArray;
        }

        $home['widget'] = $perms;
        $event->setConfig($home);
    }

    /**
     * Renders the home page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceHome($workspaceId)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $adminHomeTabConfigsTemp = $this->homeTabManager
            ->generateAdminHomeTabConfigsByWorkspace($workspace);
        $adminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigsTemp);
        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);
        $tabId = 0;

        if (count($adminHomeTabConfigs) > 0) {
            $tabId = $adminHomeTabConfigs[0]->getHomeTab()->getId();
        } elseif (count($workspaceHomeTabConfigs) > 0) {
            $tabId = $workspaceHomeTabConfigs[0]->getHomeTab()->getId();
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabs.html.twig',
            array(
                'workspace' => $workspace,
                'adminHomeTabConfigs' => $adminHomeTabConfigs,
                'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
                'tabId' => $tabId
            )
        );
    }

    /**
     * Displays the first desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopHome()
    {
        $user = $this->securityContext->getToken()->getUser();
        $adminHomeTabConfigsTemp = $this->homeTabManager
            ->generateAdminHomeTabConfigsByUser($user);
        $adminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigsTemp);
        $userHomeTabConfigs = $this->homeTabManager
            ->getVisibleDesktopHomeTabConfigsByUser($user);
        $tabId = 0;

        if (count($adminHomeTabConfigs) > 0) {
            $tabId = $adminHomeTabConfigs[0]->getHomeTab()->getId();
        } elseif (count($userHomeTabConfigs) > 0) {
            $tabId = $userHomeTabConfigs[0]->getHomeTab()->getId();
        }

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\home:desktopHomeTabs.html.twig',
            array(
                'adminHomeTabConfigs' => $adminHomeTabConfigs,
                'userHomeTabConfigs' => $userHomeTabConfigs,
                'tabId' => $tabId
            )
        );
    }
}
