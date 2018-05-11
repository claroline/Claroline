<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Workspace administration tool.
 *
 * @DI\Service()
 */
class WorkspaceListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * WorkspaceListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(
        TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Displays workspace administration tool.
     *
     * @DI\Observe("administration_tool_workspace_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:workspace\index.html.twig'
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
