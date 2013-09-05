<?php

namespace Claroline\CoreBundle\Controller;

use \Exception;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourceController
{
    private $sc;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;
    private $dispatcher;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct
    (
        SecurityContext $sc,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        Translator $translator,
        Request $request,
        StrictDispatcher $dispatcher,
        MaskManager $maskManager
    )
    {
        $this->sc = $sc;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->maskManager = $maskManager;
    }

    /**
     * @EXT\Route(
     *     "/form/{resourceType}",
     *     name="claro_resource_creation_form",
     *     options={"expose"=true}
     * )
     *
     * Renders the creation form for a given resource type.
     *
     * @param string $resourceType the resource type
     *
     * @throws \Exception
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $event = $this->dispatcher->dispatch('create_form_'.$resourceType, 'CreateFormResource');

        return new Response($event->getResponseContent());
    }

    /**
     * @EXT\Route(
     *     "/create/{resourceType}/{parentId}",
     *     name="claro_resource_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "parent",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "parentId", "strictId" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a resource.
     *
     * @param string       $resourceType the resource type
     * @param ResourceNode $parent       the parent
     * @param User         $user         the user
     *
     * @throws \Exception
     * @return Response
     */
    public function createAction($resourceType, ResourceNode $parent, User $user)
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => $resourceType));
        $this->checkAccess('CREATE', $collection);
        $event = $this->dispatcher->dispatch('create_'.$resourceType, 'CreateResource', array($resourceType));

        if (count($event->getResources()) > 0) {
            $nodesArray = array();

            foreach ($event->getResources() as $resource) {
                $createdResource = $this->resourceManager->create(
                    $resource,
                    $this->resourceManager->getResourceTypeByName($resourceType),
                    $user,
                    $parent->getWorkspace(),
                    $parent
                );

                $nodesArray[] = $this->resourceManager->toArray($createdResource->getResourceNode());
            }

            return new JsonResponse($nodesArray);
        }

        return new Response($event->getErrorFormContent());
    }

    /**
     * @EXT\Route(
     *     "/open/{resourceType}/{node}",
     *     name="claro_resource_open",
     *     options={"expose"=true}
     * )
     *
     * Opens a resource.
     *
     * @param ResourceNode $resource     the node
     * @param string       $resourceType the resource type
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function openAction(ResourceNode $node, $resourceType)
    {
        $collection = new ResourceCollection(array($node));
        //If it's a link, the resource will be its target.
        $node = $this->getRealTarget($node);
        $this->checkAccess('OPEN', $collection);
        $event = $this->dispatcher->dispatch(
            'open_'.$resourceType,
            'OpenResource',
            array($this->resourceManager->getResourceFromNode($node))
        );
        $this->dispatcher->dispatch('log', 'Log\LogResourceRead', array($node));

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="claro_resource_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * Removes a many nodes from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param array $nodes
     *
     * @return Response
     */
    public function deleteAction(array $nodes)
    {
        $collection = new ResourceCollection($nodes);
        $this->checkAccess('DELETE', $collection);

        foreach ($collection->getResources() as $node) {
            $this->resourceManager->delete($node);
        }

        return new Response('Resource deleted', 204);
    }

    /**
     * @EXT\Route(
     *     "/move/{newParent}",
     *     name="claro_resource_move",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * Moves many resource (changes their parents). This function takes an array
     * of parameters which are the ids of the moved resources
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param ResourceNode $newParent
     * @param array        $nodes
     *
     * @throws \RuntimeException
     * @return Response
     */
    public function moveAction(ResourceNode $newParent, array $nodes)
    {
        $collection = new ResourceCollection($nodes);
        $collection->addAttribute('parent', $newParent);
        $this->checkAccess('MOVE', $collection);

        foreach ($nodes as $node) {
            try {
                $movedNode = $this->resourceManager->move($node, $newParent);
                $movedNodes[] = $this->resourceManager->toArray($movedNode);
            } catch (\Gedmo\Exception\UnexpectedValueException $e) {
                throw new \RuntimeException('Cannot move a resource into itself');
            }
        }

        return new JsonResponse($movedNodes);
    }

    /**
     * @EXT\Route(
     *     "/custom/{action}/{node}",
     *     name="claro_resource_action",
     *     options={"expose"=true}
     * )
     *
     * Handles any custom action (i.e. not defined in this controller) on a
     * resource of a given type.
     *
     * @param string       $resourceType the resource type
     * @param string       $action       the action
     * @param ResourceNode $node         the resource
     *
     * @throws \Exception
     * @return Response
     */
    public function customAction($action, ResourceNode $node)
    {
        $type = $node->getResourceType();
        $menuAction = $this->maskManager
            ->getMenuFromNameAndResourceType($action, $type);

        if (!$menuAction) {
            throw new \Exception("The menu {$action} doesn't exists");
        }

        $permToCheck = $this->maskManager->getByValue($type, $menuAction->getValue());
        $eventName = $action . '_' . $type->getName();
        $collection = new ResourceCollection(array($node));
        $this->checkAccess($permToCheck->getName(), $collection);

        $event = $this->dispatcher->dispatch(
            $eventName,
            'CustomActionResource',
            array($this->resourceManager->getResourceFromNode($node))
        );

        $this->dispatcher->dispatch('log', 'Log\LogResourceCustom', array($node, $action));

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/download",
     *     name="claro_resource_download",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * This function takes an array of parameters. Theses parameters are the ids
     * of the resources which are going to be downloaded
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param array $nodes
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(array $nodes)
    {
        $collection = new ResourceCollection($nodes);
        $this->checkAccess('EXPORT', $collection);
        $file = $this->resourceManager->download($nodes);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "directory/{nodeId}",
     *     name="claro_resource_directory",
     *     options={"expose"=true},
     *     defaults={"nodeId"=0}
     * )
     * @EXT\ParamConverter(
     *      "node",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "nodeId", "strictId" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Returns a json representation of a directory, containing the following items :
     * - The path of the directory
     * - The resource types the user is allowed to create in the directory
     * - The immediate children resources of the directory which are visible for the user
     *
     * If the directory id is '0', a pseudo-directory containing the root directories
     * of the workspaces whose the user is a member is returned.
     * If the directory id is a shortcut id, the directory targeted by the shortcut
     * is returned.
     *
     * @param ResourceNode $node the directory node
     * @param User         $user the user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception if the id doesnt't match any existing directory
     */
    public function openDirectoryAction(User $user, ResourceNode $node = null)
    {
        $path = array();
        $creatableTypes = array();
        $currentRoles = $this->roleManager->getStringRolesFromCurrentUser();
        $canChangePosition = false;

        if ($node === null) {
            $nodes = $this->resourceManager->getRoots($user);
            $isRoot = true;
            $workspaceId = 0;
        } else {
            $isRoot = false;
            $workspaceId = $node->getWorkspace()->getId();
            $node = $this->getRealTarget($node);

            if ($user === $node->getCreator() || $this->sc->isGranted('ROLE_ADMIN')) {
                $canChangePosition = true;
            }

            $path = $this->resourceManager->getAncestors($node);
            $nodes = $this->resourceManager->getChildren($node, $currentRoles);
            $creatableTypes = $this->rightsManager->getCreatableTypes($currentRoles, $node);
            $this->dispatcher->dispatch('log', 'Log\LogResourceRead', array($node));
        }

        return new JsonResponse(
            array(
                'path' => $path,
                'creatableTypes' => $creatableTypes,
                'nodes' => $nodes,
                'canChangePosition' => $canChangePosition,
                'workspace_id' => $workspaceId,
                'is_root' => $isRoot
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/copy/{parent}",
     *     name="claro_resource_copy",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Adds multiple resource resource to a workspace.
     * Needs an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param ResourceNode $parent
     * @param array        $resources
     * @param User         $user
     *
     * @return Response
     */
    public function copyAction(ResourceNode $parent, array $nodes, User $user)
    {
        $newNodes = array();
        $collection = new ResourceCollection($nodes);
        $collection->addAttribute('parent', $parent);
        $this->checkAccess('COPY', $collection);

        foreach ($nodes as $node) {
            //$resource = $this->resourceManager->getResourceFromNode($node);
            $newNodes[] = $this->resourceManager
                ->toArray($this->resourceManager->copy($node, $parent, $user)->getResourceNode());
        }

        return new JsonResponse($newNodes);
    }

    /**
     * @EXT\Route(
     *     "/filter/{nodeId}",
     *     name="claro_resource_filter",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "node",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "nodeId", "strictId" = true}
     * )
     *
     * Returns a json representation of a resource search result.
     *
     * @param ResourceNode $node The id of the node from which the search was started
     *
     * @throws \Exception
     * @return Response
     */
    public function filterAction(ResourceNode $node = null)
    {
        $criteria = $this->resourceManager->buildSearchArray($this->request->query->all());
        $criteria['roots'] = isset($criteria['roots']) ? $criteria['roots'] : array();
        $path = $node ? $this->resourceManager->getAncestors($node): array();
        $userRoles = $this->roleManager->getStringRolesFromCurrentUser();

        //by criteria recursive => infinte loop
        $resources = $this->resourceManager->getByCriteria($criteria, $userRoles, true);

        return new JsonResponse(array('nodes' => $resources, 'path' => $path));
    }

    /**
     * @EXT\Route(
     *     "/shortcut/{parent}/create",
     *     name="claro_resource_create_shortcut",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("creator", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * Creates (one or several) shortcuts.
     * Takes an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param ResourceNode $parent    the new parent
     * @param User         $user      the shortcut creator
     * @param array        $resources the resources going to be linked
     *
     * @return Response
     */
    public function createShortcutAction(ResourceNode $parent, User $creator, array $nodes)
    {
        foreach ($nodes as $node) {
            $shortcut = $this->resourceManager
                ->makeShortcut($node, $parent, $creator, new ResourceShortcut());
            $links[] = $this->resourceManager->toArray($shortcut->getResourceNode());
        }

        return new JsonResponse($links);
    }

    /**
     * @EXT\Route(
     *     "restore/{parent}",
     *     name="claro_resource_restore",
     *     options={"expose"=true}
     * )
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     */
    public function restoreNodeOrderAction(ResourceNode $parent)
    {
        $this->resourceManager->restoreNodeOrder($parent);

        return new Response('success');
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Resource:breadcrumbs.html.twig")
     */
    public function renderBreadcrumbsAction(ResourceNode $node, $_breadcrumbs)
    {
        $breadcrumbsAncestors = array();

        if (count($_breadcrumbs) > 0) {
            $breadcrumbsAncestors = $this->resourceManager->getByIds($_breadcrumbs);
            $breadcrumbsAncestors[] = $node;
            $root = $breadcrumbsAncestors[0];
            $workspace = $root->getWorkspace();
        }

        //this condition is wrong
        if (count($breadcrumbsAncestors) === 0) {
            $_breadcrumbs = array();
            $ancestors = $this->resourceManager->getAncestors($node);
            $workspace = $node->getWorkspace();

            foreach ($ancestors as $ancestor) {
                $_breadcrumbs[] = $ancestor['id'];
            }

            $breadcrumbsAncestors = $this->resourceManager->getByIds($_breadcrumbs);
        }

        if (!$this->resourceManager->areAncestorsDirectory($breadcrumbsAncestors)) {
            throw new \Exception('Breadcrumbs invalid');
        };

        return array(
            'ancestors' => $breadcrumbsAncestors,
            'workspaceId' => $workspace->getId()
        );
    }

    /**
     * @EXT\Route(
     *     "/sort/{node}/next/{nextId}",
     *     name="claro_resource_insert_before",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "next",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "nextId", "strictId" = true}
     * )
     *
     * @param ResourceNode $resource
     * @param ResourceNode $next
     * @param User         $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function insertBefore(ResourceNode $node, User $user, ResourceNode $next = null)
    {
        if ($user !== $node->getParent()->getCreator() && !$this->sc->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->insertBefore($node, $next);

        return new Response('success', 204);
    }

    private function getRealTarget(ResourceNode $node)
    {
        if ($node->getClass() === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $resource = $this->resourceManager->getResourceFromNode($node);
            if ($resource === null) {
                throw new \Exception('The resource was removed.');
            }
            $node = $resource->getTarget();
            if ($node === null) {
                throw new \Exception('The node target was removed.');
            }
        }

        return $node;
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->sc->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
