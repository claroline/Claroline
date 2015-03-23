<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Defines the listening methods for all the core extension
 * points used in this plugin (tools and widgets).
 *
 * @DI\Service("hevinci.competency.plugin_listener")
 */
class PluginListener
{
    private $request;
    private $kernel;

    /**
     * @DI\InjectParams({
     *     "stack"  = @DI\Inject("request_stack"),
     *     "kernel" = @DI\Inject("http_kernel")
     * })
     *
     * @param RequestStack $stack
     */
    public function __construct(RequestStack $stack, HttpKernelInterface $kernel)
    {
        $this->request = $stack->getCurrentRequest();
        $this->kernel = $kernel;
    }

    /**
     * @DI\Observe("administration_tool_competencies")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenCompetencyTool(OpenAdministrationToolEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:Competency:frameworks', $event);
    }

    /**
     * @DI\Observe("administration_tool_learning-objectives")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenLearningObjectivesTool(OpenAdministrationToolEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:Objective:objectives', $event);
    }

    /**
     * @DI\Observe("open_tool_desktop_my-learning-objectives")
     *
     * @param DisplayToolEvent $event
     */
    public function onOpenMyLearningObjectivesTool(DisplayToolEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:MyObjective:objectives', $event);
    }

    /**
     * @DI\Observe("manage-competencies_activity")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onOpenActivityCompetencies(CustomActionResourceEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:Activity:competencies', $event);
    }

    /**
     * @DI\Observe("widget_my-learning-objectives")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplayObjectivesWidget(DisplayWidgetEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:Widget:objectives', $event);
    }

    private function forward($controller, Event $event)
    {
        $attributes = ['_controller' => $controller];

        if ($event instanceof CustomActionResourceEvent) {
            $attributes['id'] = $event->getResource()->getId();
        }

        $subRequest = $this->request->duplicate([], null, $attributes);
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        if ($event instanceof DisplayToolEvent || $event instanceof DisplayWidgetEvent) {
            $event->setContent($response->getContent());
        } else {
            $event->setResponse($response);
        }

        $event->stopPropagation();
    }
}
