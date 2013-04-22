<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\ExportWidgetConfigEvent;
use Claroline\CoreBundle\Library\Event\ImportWidgetConfigEvent;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ExportToolEvent;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;

/**
 * @DI\Service
 */
class HomeListener
{
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "templating" = @DI\Inject("templating"),
     *     "wm" = @DI\Inject("claroline.widget.manager")
     * })
     */
    public function __construct($em, $ed, $templating, $wm)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->wm = $wm;
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
                    $newEvent = new ImportWidgetConfigEvent(
                        $widgetConfig['config'],
                        $event->getWorkspace()
                    );
                    $this->ed->dispatch("widget_{$widgetConfig['name']}_from_template", $newEvent);
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
                $newEvent = new ExportWidgetConfigEvent(
                    $config->getWidget(),
                    $workspace
                );
                $this->ed->dispatch("widget_{$config->getWidget()->getName()}_to_template", $newEvent);
                if ($newEvent->getConfig() === null) {
                    throw new \Exception(
                        "The event widget_{$config->getWidget()->getName()}_to_template" .
                        " did not return any response"
                    );
                }
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
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceHome($workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\home:home.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopHome()
    {
        return $this->templating->render('ClarolineCoreBundle:Tool\desktop\home:info.html.twig');
    }
}

