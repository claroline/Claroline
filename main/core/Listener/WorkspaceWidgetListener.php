<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class WorkspaceWidgetListener
{
    private $authorization;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "templating"       = @DI\Inject("templating")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TwigEngine $templating
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("widget_my_workspaces")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->authorization->isGranted('ROLE_USER')) {
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
