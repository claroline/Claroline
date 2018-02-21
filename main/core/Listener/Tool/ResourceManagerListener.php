<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\ConfigureWorkspaceToolEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service()
 */
class ResourceManagerListener
{
    private $maskManager;
    private $resourceManager;
    private $roleManager;
    private $rightsManager;
    private $workspaceManager;
    private $userManager;
    private $tokenStorage;
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "em"                     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed"                     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"             = @DI\Inject("templating"),
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "requestStack"           = @DI\Inject("request_stack"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager"    = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "maskManager"            = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct(
        $em,
        StrictDispatcher $ed,
        $templating,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        RequestStack $requestStack,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        MaskManager $maskManager
    ) {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->maskManager = $maskManager;
    }

    /**
     * @DI\Observe("open_tool_workspace_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceResourceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceWorkspace($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("configure_workspace_tool_resource_manager")
     *
     * @param ConfigureWorkspaceToolEvent $event
     */
    public function onDisplayWorkspaceResourceConfiguration(ConfigureWorkspaceToolEvent $event)
    {
        $event->setContent($this->workspaceResourceRightsForm($event->getWorkspace()));
    }

    /**
     * @DI\Observe("open_tool_desktop_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopResourceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceDesktop());
    }

    /**
     * Renders the resources page with its layout.
     *
     * @param int $workspaceId
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     * @throws \Exception
     *
     * @return string
     */
    public function resourceWorkspace($workspaceId)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $breadcrumbsIds = $this->request->query->get('_breadcrumbs');

        if (null !== $breadcrumbsIds) {
            $ancestors = $this->resourceManager->getByIdsLevelOrder($breadcrumbsIds);

            if (!$this->resourceManager->isPathValid($ancestors)) {
                throw new \Exception('Breadcrumbs invalid');
            }
        } else {
            $ancestors = [];
        }
        $path = [];

        foreach ($ancestors as $ancestor) {
            $path[] = $this->resourceManager->toArray($ancestor, $this->tokenStorage->getToken());
        }

        $jsonPath = json_encode($path);

        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $directoryId = $this->resourceManager->getWorkspaceRoot($workspace)->getId();
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();
        $resourceActions = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findByResourceType(null);
        $defaultResourceActionsMask = $this->maskManager->getDefaultResourceActionsMask();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resources.html.twig', [
                'workspace' => $workspace,
                'directoryId' => $directoryId,
                'resourceTypes' => $resourceTypes,
                'resourceActions' => $resourceActions,
                'jsonPath' => $jsonPath,
                'maxPostSize' => ini_get('post_max_size'),
                'resourceZoom' => $this->getZoom(),
                'displayMode' => $this->getDisplayMode($workspaceId),
                'defaultResourceActionsMask' => $defaultResourceActionsMask,
             ]
        );
    }

    /**
     * Displays the resource manager.
     *
     * @return string
     */
    public function resourceDesktop()
    {
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $resourceActions = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findByResourceType(null);
        $defaultResourceActionsMask = $this->maskManager->getDefaultResourceActionsMask();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\resource_manager:resources.html.twig',
            [
                'resourceTypes' => $resourceTypes,
                'resourceActions' => $resourceActions,
                'maxPostSize' => ini_get('post_max_size'),
                'resourceZoom' => $this->getZoom(),
                'displayMode' => $this->getDisplayMode('desktop'),
                'defaultResourceActionsMask' => $defaultResourceActionsMask,
            ]
        );
    }

    public function getZoom($zoom = 'zoom100')
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        if ($this->request->getSession()->get('resourceZoom')) {
            $zoom = $this->request->getSession()->get('resourceZoom');
        }

        return $zoom;
    }

    private function workspaceResourceRightsForm(Workspace $workspace, $wsMax = 10)
    {
        if (!$this->authorization->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $resource = $this->resourceManager->getWorkspaceRoot($workspace);
        $roleRights = $this->rightsManager->getConfigurableRights($resource);
        $datas = $this->workspaceTagManager->getDatasForWorkspaceList(true);
        $resourceType = $resource->getResourceType();
        $mask = $this->maskManager->decodeMask($resourceType->getDefaultMask(), $resourceType);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resourcesRights.html.twig',
            [
                'workspace' => $workspace,
                'currentWorkspace' => $workspace,
                'resource' => $resource,
                'resourceRights' => $roleRights,
                'workspaces' => $datas['workspaces'],
                'isDir' => true,
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'workspaceRoles' => $datas['workspaceRoles'],
                'mask' => $mask,
                'wsMax' => $wsMax,
                'wsSearch' => '',
            ]
        );
    }

    private function getDisplayMode($index)
    {
        return $this->userManager->getResourceManagerDisplayMode($index);
    }
}
