<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use \Exception;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\Exception\ResourceMoveException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\LogManager;
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
    private $templating;
    private $logManager;

    /**
     * @DI\InjectParams({
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"      = @DI\Inject("templating"),
     *     "logManager"      = @DI\Inject("claroline.log.manager")
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
        MaskManager $maskManager,
        TwigEngine $templating,
        LogManager $logManager
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
        $this->templating = $templating;
        $this->logManager = $logManager;
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
        $event = $this->dispatcher->dispatch('create_'.$resourceType, 'CreateResource', array($parent, $resourceType));

        if (count($event->getResources()) > 0) {
            $nodesArray = array();

            foreach ($event->getResources() as $resource) {

                if ($event->getProcess()) {
                    $createdResource = $this->resourceManager->create(
                        $resource,
                        $this->resourceManager->getResourceTypeByName($resourceType),
                        $user,
                        $parent->getWorkspace(),
                        $parent
                    );

                    $nodesArray[] = $this->resourceManager->toArray(
                        $createdResource->getResourceNode(), $this->sc->getToken()
                    );
                } else {
                    $nodesArray[] = $this->resourceManager->toArray(
                        $resource->getResourceNode(), $this->sc->getToken()
                    );
                }
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
     * @param ResourceNode $node     the node
     * @param string       $resourceType the resource type
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function openAction(ResourceNode $node, $resourceType)
    {
        //in order to remember for later. To keep links breadcrumb working we'll need to do something like this
        //if we don't want to change to much code
        $this->request->getSession()->set('current_resource_node', $node);
    
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

        if (!$this->sc->isGranted('MOVE', $collection)) {
            $response = new Response($this->translator->trans('insufficient_permissions', array(), 'error'), 403);
            $response->headers->add(array('XXX-Claroline' => 'insufficient-permissions'));

            return $response;
        }

        foreach ($nodes as $node) {
            try {
                $movedNode = $this->resourceManager->move($node, $newParent);
                $movedNodes[] = $this->resourceManager->toArray($movedNode, $this->sc->getToken());
            } catch (ResourceMoveException $e) {
                return new Response($this->translator->trans('invalid_move', array(), 'error'), 422);
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

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/log/{node}",
     *     name="claro_resource_logs",
     *     defaults={"page" = 1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Route(
     *     "/log/{node}/{page}",
     *     name="claro_resource_logs_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Resource/logs:logList.html.twig")
     *
     * Shows resource logs list
     *
     * @param ResourceNode $node the resource
     * @param integer      $page
     *
     * @return Response
     * @throws \Exception
     */
    public function logAction(ResourceNode $node, $page)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection(array($node));
        $this->checkAccess("EDIT", $collection);

        //$type = $node->getResourceType();
        $logs = $this->logManager
            ->getResourceList($resource, $page);

        return $logs;
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
        $data = $this->resourceManager->download($nodes);
        $file = $data['file'];
        $fileName = $data['name'];
        $mimeType = $data['mimeType'];
        $response = new StreamedResponse();

        $file = $data['file'] ? : tempnam('tmp', 'tmp');
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($fileName));
        $response->headers->set('Content-Type', $mimeType);
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception if the id doesn't match any existing directory
     */
    public function openDirectoryAction(ResourceNode $node = null)
    {
        $user = $this->sc->getToken()->getUser();
        $path = array();
        $creatableTypes = array();
        $currentRoles = $this->roleManager->getStringRolesFromToken($this->sc->getToken());
        $canChangePosition = false;
        $nodesWithCreatorPerms = array();

        if ($node === null) {
            $nodesWithCreatorPerms = $this->resourceManager->getRoots($user);
            $isRoot = true;
            $workspaceId = 0;
        } else {
            $isRoot = false;
            $workspaceId = $node->getWorkspace()->getId();
            $node = $this->getRealTarget($node);
            $collection = new ResourceCollection(array($node));
            $this->checkAccess('OPEN', $collection);

            if ($user !== 'anon.') {
                if ($user === $node->getCreator() || $this->sc->isGranted('ROLE_ADMIN')) {
                    $canChangePosition = true;
                }
            }

            $path = $this->resourceManager->getAncestors($node);
            $nodes = $this->resourceManager->getChildren($node, $currentRoles);

            //set "admin" mask if someone is the creator of a resource or the resource workspace owner.
            //if someone needs admin rights, the resource type list will go in this array
            $adminTypes = [];
            $isOwner = $this->resourceManager->isWorkspaceOwnerOf($node, $this->sc->getToken());

            if ($isOwner || $this->sc->isGranted('ROLE_ADMIN')) {
                $resourceTypes = $this->resourceManager->getAllResourceTypes();

                foreach ($resourceTypes as $resourceType) {
                    $adminTypes[$resourceType->getName()] = $this->translator
                        ->trans($resourceType->getName(), array(), 'resource');
                }
            }

            foreach ($nodes as $item) {
                if ($user !== 'anon.') {
                    if ($item['creator_username'] === $user->getUsername()) {
                        $item['mask'] = 1023;
                    }
                }
                $nodesWithCreatorPerms[] = $item;
            }

            $creatableTypes = $this->rightsManager->getCreatableTypes($currentRoles, $node);
            $creatableTypes = array_merge($creatableTypes, $adminTypes);
            $this->dispatcher->dispatch('log', 'Log\LogResourceRead', array($node));
        }

        $directoryId = $node ? $node->getId() : '0';

        if ($this->request->query->has('keep-id')) {
            $this->request->getSession()->set('pickerDirectoryId', $directoryId);
        }

        $jsonResponse = new JsonResponse(
            array(
                'id' => $directoryId,
                'path' => $path,
                'creatableTypes' => $creatableTypes,
                'nodes' => $nodesWithCreatorPerms,
                'canChangePosition' => $canChangePosition,
                'workspace_id' => $workspaceId,
                'is_root' => $isRoot
            )
        );

        $jsonResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $jsonResponse->headers->add(array('Expires' => '-1'));

        return $jsonResponse;
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
     * Needs an array of ids to be functional (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param ResourceNode $parent
     * @param array        $nodes
     * @param User         $user
     *
     * @return Response
     */
    public function copyAction(ResourceNode $parent, array $nodes, User $user)
    {
        $newNodes = array();
        $collection = new ResourceCollection($nodes);
        $collection->addAttribute('parent', $parent);

        if (!$this->sc->isGranted('COPY', $collection)) {
            $response = new Response($this->translator->trans('insufficient_permissions', array(), 'error'), 403);
            $response->headers->add(array('XXX-Claroline' => 'insufficient-permissions'));

            return $response;
        }

        foreach ($nodes as $node) {
            $newNodes[] = $this->resourceManager->toArray(
                $this->resourceManager->copy($node, $parent, $user)->getResourceNode(),
                $this->sc->getToken()
            );
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
        $criteria['roots'] = $node ? array($node->getPath()) : array();
        $path = $node ? $this->resourceManager->getAncestors($node): array();
        $userRoles = $this->roleManager->getStringRolesFromToken($this->sc->getToken());

        //by criteria recursive => infinite loop
        $resources = $this->resourceManager->getByCriteria($criteria, $userRoles, true);

        return new JsonResponse(
            array(
                'id' => $node ? $node->getId() : '0',
                'nodes' => $resources,
                'path' => $path
            )
        );
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
     * @param User         $creator   the shortcut creator
     * @param array        $nodes     the resources going to be linked
     *
     * @return Response
     */
    public function createShortcutAction(ResourceNode $parent, User $creator, array $nodes)
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => 'resource_shortcut'));
        $this->checkAccess('CREATE', $collection);

        foreach ($nodes as $node) {
            $shortcut = $this->resourceManager
                ->makeShortcut($node, $parent, $creator, new ResourceShortcut());
            $links[] = $this->resourceManager->toArray($shortcut->getResourceNode(), $this->sc->getToken());
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
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param ResourceNode $parent
     * @param User         $user
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function restoreNodeOrderAction(ResourceNode $parent, User $user)
    {
        if ($user !== $parent->getCreator() && !$this->sc->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->restoreNodeOrder($parent);

        return new Response('success');
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Resource:breadcrumbs.html.twig")
     *
     * @param ResourceNode $node
     * @param integer[]    $_breadcrumbs
     *
     * @return array
     *
     * @throws Exception
     */
    public function renderBreadcrumbsAction(ResourceNode $node, array $_breadcrumbs)
    {
        //the signature method can change aswell
        //this method obviously has to change
        //this trick will never work with shortcuts to directory
        $node = $this->request->getSession()->get('current_resource_node');
        $workspace = $node->getWorkspace();
        $ancestors = $this->resourceManager->getAncestors($node);

        return array(
            'ancestors' => $ancestors,
            'workspaceId' => $workspace->getId(),
        );
        //the following code is useless and can be removed

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
     * @param ResourceNode $node
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

    /**
     * @EXT\Route(
     *     "/manager_parameters",
     *     name="claro_resource_manager_parameters",
     *     options={"expose"=true}
     * )
     */
    public function managerParametersAction()
    {
        $json = $this->templating->render(
            'ClarolineCoreBundle:Resource:managerParameters.json.twig',
            array('resourceTypes' => $this->resourceManager->getAllResourceTypes())
        );

        return new Response($json, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @EXT\Route(
     *     "/embed/{node}/{type}/{extension}/{openInNewTab}",
     *     name="claro_resource_embed",
     *     options={"expose"=true},
     *     defaults={"openInNewTab"="0"}
     * )
     *
     * Renders the HTML needed to embed a resource, based on its mime type.
     */
    public function embedResourceAction(ResourceNode $node, $type, $extension, $openInNewTab)
    {
        $view = in_array($type, array('video', 'audio', 'image')) ? $type : 'default';

        return new Response(
            $this->templating->render(
                "ClarolineCoreBundle:Resource:embed/{$view}.html.twig",
                array(
                    'node' => $node,
                    'type' => $type,
                    'extension' => $extension,
                    'openInNewTab' => $openInNewTab !== '0'
                )
            )
        );
    }

    /**
     * @EXT\Route("/zoom/{zoom}", name="claro_resource_change_zoom", options={"expose"=true})
     *
     * @param $zoom
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeZoom($zoom)
    {
        $this->request->getSession()->set('resourceZoom', $zoom);

        return new Response(200);
    }
}
