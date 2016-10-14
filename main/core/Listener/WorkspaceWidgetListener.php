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
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service()
 */
class WorkspaceWidgetListener
{
    private $authorization;
    private $templating;
    private $tokenStorage;
    private $utils;
    private $workspaceManager;
    private $workspaceTagManager;

    /**
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
    }

    /**
     * @DI\Observe("widget_my_workspaces")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $isAnon = $user === 'anon.';
        $widgetInstance = $event->getInstance();
        $mode = 0;
        $workspaces = [];
        $instanceTemplate = $widgetInstance->getTemplate();
        $template = is_null($instanceTemplate) ?
            'ClarolineCoreBundle:Widget:desktopWidgetMyWorkspaces.html.twig' :
            $instanceTemplate;

        if (!$isAnon) {
            $workspaces = $this->workspaceManager->getFavouriteWorkspacesByUser($user);

            if (count($workspaces) > 0) {
                $mode = 1;
            } else {
                $roles = $this->utils->getRoles($token);
                $datas = $this->workspaceTagManager->getDatasForWorkspaceListByUser($user, $roles);
                $workspaces = $datas['workspaces'];
            }
        }
        $content = $this->templating->render(
            $template,
            ['workspaces' => $workspaces, 'mode' => $mode, 'widgetInstance' => $widgetInstance, 'isAnon' => $isAnon]
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}
