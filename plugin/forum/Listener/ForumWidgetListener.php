<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\ForumBundle\Manager\Manager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Library\Security\Utilities;

/**
 * @DI\Service()
 */
class ForumWidgetListener
{
    /**
     * @var Manager
     */
    protected $forumManager;

    /**
     * @var WorkspaceManager
     */
    protected $workspaceManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Utilities
     */
    protected $securityUtilities;

    /**
     * @DI\InjectParams({
     *     "requestStack"      = @DI\Inject("request_stack"),
     *     "httpKernel"        = @DI\Inject("http_kernel"),
     *     "forumManager"      = @DI\Inject("claroline.manager.forum_manager"),
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "templatingEngine"  = @DI\Inject("templating"),
     *     "tokenStorage"      = @DI\Inject("security.token_storage"),
     *     "securityUtilities" = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"  = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel,
        Manager $forumManager,
        FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine,
        TokenStorageInterface $tokenStorage,
        Utilities $securityUtilities,
        WorkspaceManager $workspaceManager
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
        $this->forumManager = $forumManager;
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->tokenStorage = $tokenStorage;
        $this->securityUtilities = $securityUtilities;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @DI\Observe("widget_claroline_forum_widget")
     *
     * @param DisplayWidgetEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();
        $token = $this->tokenStorage->getToken();
        $roles = $token->getUser() !== 'anon.' ? $token->getUser()->getRoles() : $this->securityUtilities->getRoles($token);

        if ($workspace == null) {
            $templatePath = 'ClarolineForumBundle:Forum:forumsDesktopWidget.html.twig';
            $widgetType = 'desktop';
            $workspaces = $this->workspaceManager->getWorkspacesByUser($token->getUser());
        } else {
            $workspaces = array($workspace);
            $templatePath = 'ClarolineForumBundle:Forum:forumsWorkspaceWidget.html.twig';
            $widgetType = 'workspace';
        }

        $messages = $this->forumManager->getLastMessagesByWorkspacesAndRoles($workspaces, $roles);

        $event->setContent($this->templatingEngine->render(
            $templatePath,
            array(
                'widgetType' => $widgetType,
                'messages' => $messages,
            )
        ));
        $event->stopPropagation();
    }
}
