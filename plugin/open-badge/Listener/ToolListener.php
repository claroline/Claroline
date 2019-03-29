<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Listener;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Home tool.
 *
 * @DI\Service()
 */
class ToolListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "templating"    = @DI\Inject("templating"),
     *     "finder"        = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param TwigEngine                    $templating
     * @param FinderProvider                $finder
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        FinderProvider $finder,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->authorization = $authorization;
    }

    /**
     * Displays home on Desktop.
     *
     * @DI\Observe("open_tool_desktop_open-badge")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $content = $this->templating->render('ClarolineOpenBadgeBundle::desktop.html.twig', []);
        $event->setContent($content);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_workspace_open-badge")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAgenda(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $content = $this->templating->render('ClarolineOpenBadgeBundle::workspace.html.twig', ['workspace' => $workspace]);

        $event->setContent($content);
    }
}
