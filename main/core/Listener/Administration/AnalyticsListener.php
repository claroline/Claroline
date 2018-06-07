<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class AnalyticsListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * AnalyticsListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(
        TwigEngine $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * Displays analytics administration tool.
     *
     * @DI\Observe("administration_tool_platform_analytics")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:analytics.html.twig', []
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
