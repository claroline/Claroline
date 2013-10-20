<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(scope="request")
 */
class WorkspaceWidgetListener
{
    private $securityContext;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "templating"         = @DI\Inject("templating")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        TwigEngine $templating
    )
    {
        $this->securityContext = $securityContext;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("widget_my_workspaces")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:desktopWidgetMyWorkspaces.html.twig',
            array()
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}