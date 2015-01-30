<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service
 */
class PluginListener
{
    /**
     * @DI\Observe("administration_tool_hevinci_competencies")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenCompetencyTool(OpenAdministrationToolEvent $event)
    {
        $event->setResponse(new Response('Competencies tool'));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_hevinci_learning_objectives")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenLearningObjectivesTool(OpenAdministrationToolEvent $event)
    {
        $event->setResponse(new Response('Learning objectives tool'));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_hevinci_my_learning_objectives")
     *
     * @param DisplayToolEvent $event
     */
    public function onOpenMyLearningObjectivesTool(DisplayToolEvent $event)
    {
        $event->setContent('My learning objectives tool');
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("manage_competencies_activity")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onOpenActivityCompetencies(CustomActionResourceEvent $event)
    {
        $event->setResponse(new Response('Activity competency management'));
    }

    /**
     * @DI\Observe("widget_hevinci_my_learning_objectives")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplayObjectivesWidget(DisplayWidgetEvent $event)
    {
        $event->setContent('My learning objectives widget');
        $event->stopPropagation();
    }
}
