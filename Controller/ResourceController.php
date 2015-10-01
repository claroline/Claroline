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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\ImportResourcesType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\Exception\ResourceMoveException;
use Claroline\CoreBundle\Manager\Exception\ResourceNotFoundExcetion;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Manager\TransfertManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourceController
{
    private $tokenStorage;
    private $authorization;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;
    private $dispatcher;
    private $maskManager;
    private $templating;
    private $logManager;
    private $fileManager;
    private $transferManager;
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"      = @DI\Inject("templating"),
     *     "logManager"      = @DI\Inject("claroline.log.manager"),
    *      "fileManager"     = @DI\Inject("claroline.manager.file_manager"),
     *     "transferManager" = @DI\Inject("claroline.manager.transfert_manager"),
     *     "formFactory"     = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        Request $request,
        StrictDispatcher $dispatcher,
        MaskManager $maskManager,
        TwigEngine $templating,
        LogManager $logManager,
        FileManager $fileManager,
        TransfertManager $transferManager,
        FormFactory $formFactory
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->maskManager = $maskManager;
        $this->templating = $templating;
        $this->logManager = $logManager;
        $this->fileManager = $fileManager;
        $this->transferManager = $transferManager;
        $this->formFactory = $formFactory;
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
     *     "/create/{resourceType}/{parentId}/published/{published}",
     *     name="claro_resource_create",
     *     defaults={"published"=0},
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
    public function createAction(
        $resourceType,
        ResourceNode $parent,
        User $user,
        $published = 0
    )
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => $resourceType));

        if (!$this->authorization->isGranted('CREATE', $collection)) {
            $errors = $collection->getErrors();
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                array('errors' => $errors)
            );

            $response = new Response($content, 403);
            $response->headers->add(array('XXX-Claroline' => 'resource-error'));

            return $response;
        }

        $event = $this->dispatcher->dispatch('create_'.$resourceType, 'CreateResource', array($parent, $resourceType));
        $isPublished = intval($published) === 1 ? true : $event->isPublished();

        if (count($event->getResources()) > 0) {
            $nodesArray = array();

            foreach ($event->getResources() as $resource) {

                if ($event->getProcess()) {
                    $createdResource = $this->resourceManager->create(
                        $resource,
                        $this->resourceManager->getResourceTypeByName($resourceType),
                        $user,
                        $parent->getWorkspace(),
                        $parent,
                        null,
                        array(),
                        $isPublished
                    );
                    $this->dispatcher->dispatch(
                        'resource_created_' . $resourceType,
                        'ResourceCreated',
                        array($createdResource->getResourceNode())
                    );

                    $nodesArray[] = $this->resourceManager->toArray(
                        $createdResource->getResourceNode(), $this->tokenStorage->getToken()
                    );
                } else {
                    $nodesArray[] = $this->resourceManager->toArray(
                        $resource->getResourceNode(), $this->tokenStorage->getToken()
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
     * @EXT\Route(
     *     "/open/{node}",
     *     name="claro_resource_open_short",
     *     requirements={"node" = "\d+"},
     *     defaults={"resourceType" = null},
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
    public function openAction(ResourceNode $node, $resourceType = null)
    {
        //in order to remember for later. To keep links breadcrumb working we'll need to do something like this
        //if we don't want to change to much code
        $this->request->getSession()->set('current_resource_node', $node);

        //double check... first the resource, then the target
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('OPEN', $collection);
        //If it's a link, the resource will be its target.
        $node = $this->getRealTarget($node);
        $this->checkAccess('OPEN', $collection);
        if ($resourceType === null) {
            $resourceType = $node->getResourceType()->getName();
        }
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

        if (!$this->authorization->isGranted('MOVE', $collection)) {
            $errors = $collection->getErrors();
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                array('errors' => $errors)
            );

            $response = new Response($content, 403);
            $response->headers->add(array('XXX-Claroline' => 'resource-error'));

            return $response;
        }

        foreach ($nodes as $node) {
            try {
                $movedNode = $this->resourceManager->move($node, $newParent);
                $movedNodes[] = $this->resourceManager->toArray($movedNode, $this->tokenStorage->getToken());
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
     * If the ResourceType is null, it's an action (resource action) valides for all type of resources.
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

        $collection = new ResourceCollection(array($node));

        if ($menuAction->getResourceType() === null) {
            if (!$this->authorization->isGranted('ROLE_USER')) {
                throw new AccessDeniedException('You must be log in to execute this action !');
            }
            $this->checkAccess('open', $collection);
            $eventName = 'resource_action_' . $action;
        } else {
            $permToCheck = $this->maskManager->getByValue($type, $menuAction->getValue());
            $this->checkAccess($permToCheck->getName(), $collection);
            $eventName = $action . '_' . $type->getName();
        }

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
        $this->checkAccess("ADMINISTRATE", $collection);

        //$type = $node->getResourceType();
        $logs = $this->logManager->getResourceList($resource, $page);

        return $logs;
    }

    /**
     * @EXT\Route(
     *     "/download",
     *     name="claro_resource_download",
     *     options={"expose"=true},
     *     defaults ={"forceArchive"=false}
     * )
     * @EXT\Route(
     *     "/download/{forceArchive}",
     *     name="claro_resource_download",
     *     options={"expose"=true},
     *     requirements={"forceArchive" = "^(true|false|0|1)$"},
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
     * @param bool $forceArchive
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(array $nodes, $forceArchive = false)
    {
        $collection = new ResourceCollection($nodes);
        $this->checkAccess('EXPORT', $collection);
        $data = $this->resourceManager->download($nodes, $forceArchive);
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
        $response->send();

        return new Response();
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
        $user = $this->tokenStorage->getToken()->getUser();
        $path = array();
        $creatableTypes = array();
        $currentRoles = $this->roleManager->getStringRolesFromToken($this->tokenStorage->getToken());
        $canChangePosition = false;
        $nodesWithCreatorPerms = array();

        if ($node === null) {
            $nodes = $this->resourceManager->getRoots($user);
            $isRoot = true;
            $workspaceId = 0;

            foreach ($nodes as $el) {
                $item = $el;
                $dateModification = $el['modification_date'];
                $item['modification_date'] = $dateModification->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));;
                $dateCreation = $el['creation_date'];
                $item['creation_date'] = $dateCreation->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));;
                $nodesWithCreatorPerms[] = $item;
            }
        } else {
            $isRoot = false;
            $workspaceId = $node->getWorkspace()->getId();
            $isPws = $node->getWorkspace()->isPersonal();
            $node = $this->getRealTarget($node);
            $collection = new ResourceCollection(array($node));
            $this->checkAccess('OPEN', $collection);

            if ($user !== 'anon.') {
                if ($user === $node->getCreator() || $this->authorization->isGranted('ROLE_ADMIN')) {
                    $canChangePosition = true;
                }
            }

            $path = $this->resourceManager->getAncestors($node);
            $nodes = $this->resourceManager->getChildren($node, $currentRoles, $user, true);

            //set "admin" mask if someone is the creator of a resource or the resource workspace owner.
            //if someone needs admin rights, the resource type list will go in this array
            $adminTypes = [];
            $isOwner = $this->resourceManager->isWorkspaceOwnerOf($node, $this->tokenStorage->getToken());

            if ($isOwner || $this->authorization->isGranted('ROLE_ADMIN')) {
                $resourceTypes = $this->resourceManager->getAllResourceTypes();

                foreach ($resourceTypes as $resourceType) {
                    $adminTypes[$resourceType->getName()] = $this->translator
                        ->trans($resourceType->getName(), array(), 'resource');
                }
            }

            $enableRightsEdition = true;

            if ($isPws && !$this->rightsManager->canEditPwsPerm($this->tokenStorage->getToken())) {
                $enableRightsEdition = false;
            }

            //get the file list in that directory to know their size.
            $files = $this->fileManager->getDirectoryChildren($node);

            foreach ($nodes as $el) {
                $item = $el;
                if ($user !== 'anon.') {
                    if ($item['creator_username'] === $user->getUsername()
                        && !$this->isUsurpatingWorkspaceRole($this->tokenStorage->getToken()) ) {
                        $item['mask'] = 32767;
                    }
                }

                $item['new'] = true;
                $item['enableRightsEdition'] = $enableRightsEdition;
                $dateModification = $el['modification_date'];
                $item['modification_date'] = $dateModification->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));;
                $dateCreation = $el['creation_date'];
                $item['timestamp_last_modification'] = $dateModification->getTimeStamp();
                if (isset ($el['last_opened'])) {
                    $item['last_opened'] = $el['last_opened']->getTimeStamp();
                    if ($item['last_opened'] >= $item['timestamp_last_modification']) $item['new'] = false;
                }
                $item['creation_date'] = $dateCreation->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));;

                foreach ($files as $file) {
                    if ($file->getResourceNode()->getId() === $el['id']) {
                        $item['size'] = $file->getFormattedSize();
                    }
                }

                //compute this is_published flag. If the resource has an accessible_from/accessible_until flag
                //and the current date don't match, then it's de facto unpublished.
                if ($item['accessible_from'] || $item['accessible_until']) {
                    $now = new \DateTime();
                    if ($item['accessible_from']->getTimeStamp() > $now->getTimeStamp()) $item['published'] = false;
                    if ($item['accessible_until']->getTimeStamp() < $now->getTimeStamp()) $item['published'] = false;
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

        foreach($nodesWithCreatorPerms as &$element) {
            $element['path_for_display'] = ResourceNode::convertPathForDisplay($element['path']);
        }

        unset($element);

        $jsonResponse = new JsonResponse(
            array(
                'id'                => $directoryId,
                'path'              => $path,
                'creatableTypes'    => $creatableTypes,
                'nodes'             => $nodesWithCreatorPerms,
                'canChangePosition' => $canChangePosition,
                'workspace_id'      => $workspaceId,
                'is_root'           => $isRoot
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

        if (!$this->authorization->isGranted('COPY', $collection)) {
            $errors = $collection->getErrors();
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                array('errors' => $errors)
            );

            $response = new Response($content, 403);
            $response->headers->add(array('XXX-Claroline' => 'resource-error'));

            return $response;
            }

        $i = 1;

        try {
            foreach ($nodes as $node) {
                $newNodes[] = $this->resourceManager->toArray(
                    $this->resourceManager->copy($node, $parent, $user, $i)->getResourceNode(),
                    $this->tokenStorage->getToken()
                );
                $i++;
            }
        } catch (ResourceNotFoundExcetion $e) {
            $errors = array($e->getMessage());
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                array('errors' => $errors)
            );
            $response = new Response($content, 403);
            $response->headers->add(array('XXX-Claroline' => 'resource-error'));

            return $response;
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
        $userRoles = $this->roleManager->getStringRolesFromToken($this->tokenStorage->getToken());

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
            $links[] = $this->resourceManager->toArray($shortcut->getResourceNode(), $this->tokenStorage->getToken());
        }

        return new JsonResponse($links);
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
        //this trick will never work with shortcuts to directory
        //we don't support directory links anymore
        $nodeFromSession = $this->request->getSession()->get('current_resource_node');
        $node = $nodeFromSession !== null ? $nodeFromSession: $node;
        $workspace = $node->getWorkspace();
        $ancestors = $this->resourceManager->getAncestors($node);

        return array(
            'ancestors' => $ancestors,
            'workspaceId' => $workspace->getId(),
        );
    }


    /**
     * @EXT\Route(
     *     "/sort/{node}/at/{index}",
     *     name="claro_resource_insert_at",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param ResourceNode $node
     * @param User         $user
     * @param integer      $index
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function insertAt(ResourceNode $node, User $user, $index)
    {
        if ($user !== $node->getParent()->getCreator() && !$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->insertAtIndex($node, $index);

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
        if (!$this->authorization->isGranted($permission, $collection)) {
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
        $response = new Response('', 401, array('Content-Type' => 'application/json'));
        if ($this->authorization->isGranted('ROLE_USER')) {
            $json = $this->templating->render(
                'ClarolineCoreBundle:Resource:managerParameters.json.twig',
                array('resourceTypes' => $this->resourceManager->getAllResourceTypes())
            );
            $response
                ->setContent($json)
                ->setStatusCode(200);
        }

        return $response;
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

    /**
     * @EXT\Route(
     *     "/export",
     *     name="claro_resource_export",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * This function takes an array of parameters. Theses parameters are the ids
     * of the resources which are going to be exported
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param array $nodes
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction(array $nodes)
    {
        if (count($nodes) === 0) {
            throw new \Exception('No resource to export');
        }

        $workspace = $nodes[0]->getWorkspace();
        $archive = $this->transferManager->exportResources($workspace, $nodes);
        $fileName = $workspace->getCode() . '.zip';

        $mimeType = 'application/zip';
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($archive) {
                readfile($archive);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($fileName));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', filesize($archive));
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/{node}/import/form",
     *     name="claro_resource_import_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:importModalForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importFormAction(ResourceNode $node)
    {
        $form = $this->formFactory->create(new ImportResourcesType());

        return array('form' => $form->createView(), 'directory' => $node);
    }

    /**
     * @EXT\Route(
     *     "/{directory}/import",
     *     name="claro_resource_import",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:importModalForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction(ResourceNode $directory)
    {
        $form = $this->formFactory->create(new ImportResourcesType());
        $form->handleRequest($this->request);

        /*try {*/
            if ($form->isValid()) {
                $template = $form->get('file')->getData();
                $config = Configuration::fromTemplate($template);
                $user = $this->tokenStorage->getToken()->getUser();
                $this->transferManager->importResources($config, $user, $directory);
                $this->transferManager->importRichText();

                return new JsonResponse(array());
            } else {

                return array('form' => $form->createView(), 'directory' => $directory);
            }/*
        } catch (\Exception $e) {
            $errorMsg = $this->translator->trans(
                'invalid_file',
                array(),
                'platform'
            );
            $form->addError(new FormError($e->getMessage()));*/

            return array('form' => $form->createView(), 'directory' => $directory);
        //}
    }

    public function deleteNodeConfirmAction(ResourceNode $node)
    {
        throw new \Exception('hey');
    }

    private function isUsurpatingWorkspaceRole(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role->getRole() === 'ROLE_USURPATE_WORKSPACE_ROLE') {
                return true;
            }
        }

        return false;
    }
}
