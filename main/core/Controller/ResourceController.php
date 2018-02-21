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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Form\ImportResourcesType;
use Claroline\CoreBundle\Form\Resource\UnlockType;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Exception\ResourceMoveException;
use Claroline\CoreBundle\Manager\Exception\ResourceNotFoundExcetion;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\TransferManager;
use Claroline\CoreBundle\Manager\UserManager;
use Exception;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class ResourceController extends Controller
{
    private $tokenStorage;
    private $authorization;
    private $resourceManager;
    private $resourceNodeManager;
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
    private $userManager;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"         = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"       = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"          = @DI\Inject("translator"),
     *     "request"             = @DI\Inject("request"),
     *     "dispatcher"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"          = @DI\Inject("templating"),
     *     "logManager"          = @DI\Inject("claroline.log.manager"),
     *     "fileManager"         = @DI\Inject("claroline.manager.file_manager"),
     *     "transferManager"     = @DI\Inject("claroline.manager.transfer_manager"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
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
        TransferManager $transferManager,
        FormFactory $formFactory,
        UserManager $userManager,
        EventDispatcherInterface $eventDispatcher,
        ResourceNodeManager $resourceNodeManager
    ) {
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
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceNodeManager = $resourceNodeManager;
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
     *
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
     *
     * @return Response
     */
    public function createAction(
        $resourceType,
        ResourceNode $parent,
        User $user,
        $published = 0
    ) {
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes(['type' => $resourceType]);

        if (!$this->authorization->isGranted('CREATE', $collection)) {
            $errors = $collection->getErrors();
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                ['errors' => $errors]
            );

            $response = new Response($content, 403);
            $response->headers->add(['XXX-Claroline' => 'resource-error']);

            return $response;
        }

        $event = $this->dispatcher->dispatch('create_'.$resourceType, 'CreateResource', [$parent, $resourceType]);
        $isPublished = 1 === intval($published) ? true : $event->isPublished();

        if (count($event->getResources()) > 0) {
            $nodesArray = [];

            foreach ($event->getResources() as $resource) {
                if ($event->getProcess()) {
                    $createdResource = $this->resourceManager->create(
                        $resource,
                        $this->resourceManager->getResourceTypeByName($resourceType),
                        $user,
                        $parent->getWorkspace(),
                        $parent,
                        null,
                        [],
                        $isPublished
                    );
                    $this->dispatcher->dispatch(
                        'resource_created_'.$resourceType,
                        'ResourceCreated',
                        [$createdResource->getResourceNode()]
                    );

                    $nodesArray[] = $this->resourceManager->toArray(
                        $createdResource->getResourceNode(),
                        $this->tokenStorage->getToken()
                    );
                } else {
                    $nodesArray[] = $this->resourceManager->toArray(
                        $resource->getResourceNode(),
                        $this->tokenStorage->getToken()
                    );
                }
            }

            return new JsonResponse($nodesArray);
        }

        return new Response($event->getErrorFormContent());
    }

    /**
     * Opens a resource.
     *
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
     * @param ResourceNode $node         the node
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

        // Fetch workspace details, otherwise it won't store them in session.
        // I know it's not pretty but it's the only way
        // I could think of to load them before the node gets stored is session
        if ($node->getWorkspace()) {
            $options = $node->getWorkspace()->getOptions();

            if ($options) {
                $options->getDetails();
            }
        }
        $this->request->getSession()->set('current_resource_node', $node);
        $isIframe = (bool) $this->request->query->get('iframe');
        //double check... first the resource, then the target
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('OPEN', $collection);
        //If it's a link, the resource will be its target.
        $node = $this->getRealTarget($node);
        $this->checkAccess('OPEN', $collection);
        if (null === $resourceType) {
            $resourceType = $node->getResourceType()->getName();
        }
        $event = $this->dispatcher->dispatch(
            'open_'.$resourceType,
            'OpenResource',
            [$this->resourceManager->getResourceFromNode($node), $isIframe]
        );
        $this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$node]);

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
     *     "/publish",
     *     name="claro_resource_publish",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * Publishes many nodes from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @todo to be merge with ResourceNodeController::publishAction (works with UUIDs)
     *
     * @param array $nodes
     *
     * @return Response
     */
    public function publishAction(array $nodes)
    {
        $collection = new ResourceCollection($nodes);
        $this->checkAccess('ADMINISTRATE', $collection);
        $this->resourceManager->setPublishedStatus($nodes, true);

        return new Response('Resources published', 204);
    }

    /**
     * @EXT\Route(
     *     "/unpublish",
     *     name="claro_resource_unpublish",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "nodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds" = true}
     * )
     *
     * Unpublishes many nodes from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @todo to be merge with ResourceNodeController::unpublishAction (works with UUIDs)
     *
     * @param array $nodes
     *
     * @return Response
     */
    public function unpublishAction(array $nodes)
    {
        $collection = new ResourceCollection($nodes);
        $this->checkAccess('ADMINISTRATE', $collection);
        $this->resourceManager->setPublishedStatus($nodes, false);

        return new Response('Resources unpublished', 204);
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
     *
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
                ['errors' => $errors]
            );

            $response = new Response($content, 403);
            $response->headers->add(['XXX-Claroline' => 'resource-error']);

            return $response;
        }

        foreach ($nodes as $node) {
            try {
                $movedNode = $this->resourceManager->move($node, $newParent);
                $movedNodes[] = $this->resourceManager->toArray($movedNode, $this->tokenStorage->getToken());
            } catch (ResourceMoveException $e) {
                return new Response($this->translator->trans('invalid_move', [], 'error'), 422);
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
     * @param string       $action the action
     * @param ResourceNode $node   the resource
     *
     * @throws \Exception
     *
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

        $collection = new ResourceCollection([$node]);

        if (null === $menuAction->getResourceType()) {
            if (!$this->authorization->isGranted('ROLE_USER')) {
                throw new AccessDeniedException('You must be log in to execute this action !');
            }
            $this->checkAccess('open', $collection);
            $eventName = 'resource_action_'.$action;
        } else {
            $permToCheck = $this->maskManager->getByValue($type, $menuAction->getValue());
            $this->checkAccess($permToCheck->getName(), $collection);
            $eventName = $action.'_'.$type->getName();
        }

        $event = $this->dispatcher->dispatch(
            $eventName,
            'CustomActionResource',
            [$this->resourceManager->getResourceFromNode($node)]
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
     * @param int          $page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logAction(ResourceNode $node, $page)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);
        $logs = $this->logManager->getResourceList($resource, $page);

        return $logs;
    }

    /**
     * @EXT\Route(
     *     "/log/{node}/csv",
     *     name="claro_resource_logs_csv",
     *     requirements={"node" = "\d+"}
     * )
     *
     * @param ResourceNode $node the resource
     *
     * @return Response
     */
    public function logCSVAction(ResourceNode $node)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);

        $response = new StreamedResponse(function () use ($resource) {
            $resourceList = $this->logManager->getResourceList($resource);
            $results = $resourceList['results'];
            $date_format = $this->translator->trans('date_format', [], 'platform');
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, [
                $this->translator->trans('date', [], 'platform'),
                $this->translator->trans('action', [], 'platform'),
                $this->translator->trans('user', [], 'platform'),
                $this->translator->trans('action', [], 'platform'),
            ]);
            foreach ($results as $result) {
                fputcsv($handle, [
                    $result->getDateLog()->format($date_format).' '.$result->getDateLog()->format('H:i'),
                    $this->translator->trans('log_'.$result->getAction().'_shortname', [], 'log'),
                    $this->str_to_csv($this->renderView('ClarolineCoreBundle:Log:view_list_item_doer.html.twig', ['log' => $result])),
                    $this->str_to_csv($this->renderView('ClarolineCoreBundle:Log:view_list_item_sentence.html.twig', [
                        'log' => $result,
                        'listItemView' => array_key_exists($result->getId(), $resourceList['listItemViews']) ? $resourceList['listItemViews'][$result->getId()] : null,
                    ])),
                ]);
            }

            fclose($handle);
        });
        $dateStr = date('YmdHis');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="actions_'.$dateStr.'.csv"');

        return $response;
    }

    /**
     * @param string $string
     *
     * @return string
     *
     * Sanitize a string by removing html tags, multiple spaces and new lines
     */
    private function str_to_csv($string)
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($string)));
    }

    /**
     * @EXT\Route(
     *     "/log/{node}/user",
     *     name="claro_resource_logs_by_user",
     *     defaults={"page" = 1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Route(
     *     "/log/{node}/user/{page}",
     *     name="claro_resource_logs_by_user_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Resource/logs:logByUser.html.twig")
     *
     * Shows resource logs list
     *
     * @param ResourceNode $node the resource
     * @param int          $page
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logByUserAction(ResourceNode $node, $page)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);

        return $this->logManager->countByUserResourceList($resource, $page);
    }

    /**
     * @EXT\Route(
     *     "/log/{node}/user/csv",
     *     name="claro_resource_logs_by_user_csv"
     * )
     *
     * Exports in CSV the list of user actions for the given criteria
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $node
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function logByUserCSVAction(ResourceNode $node)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);

        $logManager = $this->logManager;

        $response = new StreamedResponse(function () use ($logManager, $resource) {
            $results = $logManager->countByUserListForCSV('workspace', null, $resource);
            $handle = fopen('php://output', 'w+');
            while (false !== ($row = $results->next())) {
                // add a line in the csv file. You need to implement a toArray() method
                // to transform your object into an array
                fputcsv($handle, [$row[$results->key()]['name'], $row[$results->key()]['actions']]);
            }

            fclose($handle);
        });
        $dateStr = date('YmdHis');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="user_actions_'.$dateStr.'.csv"');

        return $response;
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
     * @param bool  $forceArchive
     *
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

        $file = $data['file'] ?: tempnam('tmp', 'tmp');
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($fileName));
        if (null !== $mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }
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
        $path = [];
        $creatableTypes = [];
        $currentRoles = $this->roleManager->getStringRolesFromToken($this->tokenStorage->getToken());
        $canChangePosition = false;
        $nodesWithCreatorPerms = [];

        if (null === $node) {
            $nodes = $this->resourceManager->getRoots($user);
            $isRoot = true;
            $workspaceId = 0;

            foreach ($nodes as $el) {
                $item = $el;
                $dateModification = $el['modification_date'];
                $item['modification_date'] = $dateModification->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));
                $dateCreation = $el['creation_date'];
                $item['creation_date'] = $dateCreation->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));
                $nodesWithCreatorPerms[] = $item;
            }
        } else {
            $isRoot = false;
            $workspaceId = $node->getWorkspace()->getId();
            $node = $this->getRealTarget($node);
            $collection = new ResourceCollection([$node]);
            $this->checkAccess('OPEN', $collection);
            $canAdministrate = $this->authorization->isGranted('ADMINISTRATE', $node);

            if ('anon.' !== $user) {
                if ($user === $node->getCreator() || $this->authorization->isGranted('ROLE_ADMIN')
                    || $canAdministrate
                ) {
                    $canChangePosition = true;
                }
            }

            $path = $this->resourceManager->getAncestors($node);
            // Disable lastOpenDate for now, until a better logging system is implemented
            $nodes = $this->resourceManager->getChildren($node, $currentRoles, $user, false, $canAdministrate);

            //set "admin" mask if someone is the creator of a resource or the resource workspace owner.
            //if someone needs admin rights, the resource type list will go in this array
            $adminTypes = [];
            $isOwner = $this->resourceManager->isWorkspaceOwnerOf($node, $this->tokenStorage->getToken());

            if ($isOwner || $this->authorization->isGranted('ROLE_ADMIN')) {
                $resourceTypes = $this->resourceManager->getAllResourceTypes();

                foreach ($resourceTypes as $resourceType) {
                    $adminTypes[$resourceType->getName()] = $this->translator
                        ->trans($resourceType->getName(), [], 'resource');
                }
            }

            $enableRightsEdition = true;

            //get the file list in that directory to know their size.
            $files = $this->fileManager->getDirectoryChildren($node);

            foreach ($nodes as $el) {
                $item = $el;
                if ('anon.' !== $user) {
                    if ($item['creator_username'] === $user->getUsername()
                        && !$this->isUsurpatingWorkspaceRole($this->tokenStorage->getToken())) {
                        $item['mask'] = 32767;
                    }
                }

                $item['new'] = true;
                $item['enableRightsEdition'] = $enableRightsEdition;
                $dateModification = $el['modification_date'];
                $item['modification_date'] = $dateModification->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));
                $dateCreation = $el['creation_date'];
                $item['timestamp_last_modification'] = $dateModification->getTimeStamp();
                if (isset($el['last_opened'])) {
                    $item['last_opened'] = $el['last_opened']->getTimeStamp();
                    if ($item['last_opened'] >= $item['timestamp_last_modification']) {
                        $item['new'] = false;
                    }
                }
                $item['creation_date'] = $dateCreation->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));

                foreach ($files as $file) {
                    if ($file->getResourceNode()->getId() === $el['id']) {
                        $item['size'] = $file->getFormattedSize();
                    }
                }

                //compute this is_published flag. If the resource has an accessible_from/accessible_until flag
                //and the current date don't match, then it's de facto unpublished.
                if ($item['accessible_from'] || $item['accessible_until']) {
                    $now = new \DateTime();
                    if ($item['accessible_from']) {
                        if ($item['accessible_from']->getTimeStamp() > $now->getTimeStamp()) {
                            $item['published'] = false;
                        }
                    }
                    if ($item['accessible_until']) {
                        if ($item['accessible_until']->getTimeStamp() < $now->getTimeStamp()) {
                            $item['published'] = false;
                        }
                    }
                }

                $nodesWithCreatorPerms[] = $item;
            }

            $creatableTypes = $this->rightsManager->getCreatableTypes($currentRoles, $node);
            $creatableTypes = array_merge($creatableTypes, $adminTypes);
            asort($creatableTypes);
            $this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$node]);
        }

        $directoryId = $node ? $node->getId() : '0';

        if ($this->request->query->has('keep-id')) {
            $this->request->getSession()->set('pickerDirectoryId', $directoryId);
        }

        foreach ($nodesWithCreatorPerms as &$element) {
            $element['path_for_display'] = ResourceNode::convertPathForDisplay($element['path']);
        }

        $jsonResponse = new JsonResponse(
            [
                'id' => $directoryId,
                'path' => $path,
                'creatableTypes' => $creatableTypes,
                'nodes' => $nodesWithCreatorPerms,
                'canChangePosition' => $canChangePosition,
                'workspace_id' => $workspaceId,
                'is_root' => $isRoot,
            ]
        );

        $jsonResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $jsonResponse->headers->add(['Expires' => '-1']);

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
        $newNodes = [];
        $collection = new ResourceCollection($nodes);
        $collection->addAttribute('parent', $parent);

        if (!$this->authorization->isGranted('COPY', $collection)) {
            $errors = $collection->getErrors();
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                ['errors' => $errors]
            );

            $response = new Response($content, 403);
            $response->headers->add(['XXX-Claroline' => 'resource-error']);

            return $response;
        }

        $i = 1;

        try {
            foreach ($nodes as $node) {
                $newNodes[] = $this->resourceManager->toArray(
                    $this->resourceManager->copy($node, $parent, $user, $i)->getResourceNode(),
                    $this->tokenStorage->getToken()
                );
                ++$i;
            }
        } catch (ResourceNotFoundExcetion $e) {
            $errors = [$e->getMessage()];
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:errors.html.twig',
                ['errors' => $errors]
            );
            $response = new Response($content, 403);
            $response->headers->add(['XXX-Claroline' => 'resource-error']);

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
     *
     * @return Response
     */
    public function filterAction(ResourceNode $node = null)
    {
        $criteria = $this->resourceManager->buildSearchArray($this->request->query->all());
        $criteria['roots'] = $node ? [$node->getPath()] : [];
        // Display only active resources (omit soft deleted)
        $criteria['active'] = true;
        $path = $node ? $this->resourceManager->getAncestors($node) : [];
        $userRoles = $this->roleManager->getStringRolesFromToken($this->tokenStorage->getToken());

        //by criteria recursive => infinite loop
        $resources = $this->resourceManager->getByCriteria($criteria, $userRoles);

        //if a search option has been provided, tagged resources are also fetched
        if (isset($criteria['name'])) {
            $search = $criteria['name'];
            //retrieve all resources that respect the criteria except the search to generate a whitelist
            unset($criteria['name']);
            $unsearchedResources = $this->resourceManager->getByCriteria($criteria, $userRoles);
            $ids = [];

            foreach ($unsearchedResources as $resource) {
                $ids[] = $resource['id'];
            }
            $options = [
                'tag' => $search,
                'strict' => false,
                'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceNode',
                'object_response' => true,
                'ordered_by' => 'name',
                'ids' => $ids,
            ];
            $event = $this->eventDispatcher->dispatch('claroline_retrieve_tagged_objects', new GenericDataEvent($options));
            $taggedResources = $event->getResponse();
            $resources = $this->mergeSearchedResources($resources, $taggedResources);
        }

        return new JsonResponse(
            [
                'id' => $node ? $node->getId() : '0',
                'nodes' => $resources,
                'path' => $path,
            ]
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
     * @param ResourceNode $parent  the new parent
     * @param User         $creator the shortcut creator
     * @param array        $nodes   the resources going to be linked
     *
     * @return Response
     */
    public function createShortcutAction(ResourceNode $parent, User $creator, array $nodes)
    {
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes(['type' => 'resource_shortcut']);
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
     * @param int[]        $_breadcrumbs
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
        $node = null !== $nodeFromSession ? $nodeFromSession : $node;
        $workspace = $node->getWorkspace();
        $ancestors = $this->resourceManager->getAncestors($node);

        return [
            'ancestors' => $ancestors,
            'workspaceId' => $workspace->getId(),
        ];
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
     * @param int          $index
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function insertAt(ResourceNode $node, User $user, $index)
    {
        if ($user !== $node->getParent()->getCreator() && !$this->authorization->isGranted('ROLE_ADMIN')
            && !$this->authorization->isGranted('ADMINISTRATE', $node->getParent())
        ) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->insertAtIndex($node, $index);

        return new Response('success', 204);
    }

    private function getRealTarget(ResourceNode $node)
    {
        if ('Claroline\CoreBundle\Entity\Resource\ResourceShortcut' === $node->getClass()) {
            $resource = $this->resourceManager->getResourceFromNode($node);
            if (null === $resource) {
                throw new \Exception('The resource was removed.');
            }
            $node = $resource->getTarget();
            if (null === $node) {
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
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
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
        $response = new Response('', 401, ['Content-Type' => 'application/json']);
        if ($this->authorization->isGranted('ROLE_USER')) {
            $json = $this->templating->render(
                'ClarolineCoreBundle:Resource:managerParameters.json.twig',
                [
                    'resourceTypes' => $this->resourceManager->getAllResourceTypes(),
                    'defaultResourceActionsMask' => $this->maskManager->getDefaultResourceActionsMask(),
                ]
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
        $view = in_array($type, ['video', 'audio', 'image']) ? $type : 'default';

        return new Response(
            $this->templating->render(
                "ClarolineCoreBundle:Resource:embed/{$view}.html.twig",
                [
                    'node' => $node,
                    'resource' => $this->resourceManager->getResourceFromNode($node),
                    'type' => $type,
                    'extension' => $extension,
                    'openInNewTab' => '0' !== $openInNewTab,
                ]
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
        if (0 === count($nodes)) {
            throw new \Exception('No resource to export');
        }

        $workspace = $nodes[0]->getWorkspace();
        $archive = $this->transferManager->exportResources($workspace, $nodes);
        $fileName = $workspace->getCode().'.zip';

        $mimeType = 'application/zip';
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($archive) {
                readfile($archive);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($fileName));
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

        return ['form' => $form->createView(), 'directory' => $node];
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

        if ($form->isValid()) {
            $template = $form->get('file')->getData();
            $user = $this->tokenStorage->getToken()->getUser();
            $this->transferManager->importResources($template, $user, $directory);

            return new JsonResponse([]);
        }

        return ['form' => $form->createView(), 'directory' => $directory];
    }

    /**
     * @EXT\Route(
     *     "/resource/manager/{index}/display/mode/{displayMode}/register",
     *     name="claro_resource_manager_display_mode_register",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceManagerDisplayModeRegisterAction($index, $displayMode, User $user = null)
    {
        if (!is_null($user)) {
            $this->userManager->registerResourceManagerDisplayModeByUser($user, $index, $displayMode);
        }

        return new Response(200);
    }

    //this method is not routed and called from the Resource/layout.html.twig file

    /**
     * @EXT\Template("ClarolineCoreBundle:Resource:unlockCodeForm.html.twig")
     */
    public function unlockCodeFormAction(ResourceNode $node)
    {
        $form = $this->formFactory->create(new UnlockType());

        return ['form' => $form->createView(), 'node' => $node];
    }

    /**
     * @EXT\Route(
     *     "/resource/{node}/unlock",
     *     name="claro_resource_form_unlock",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unlockCodeAction(ResourceNode $node)
    {
        $form = $this->formFactory->create(new UnlockType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $code = $form->get('code')->getData();
            $this->resourceNodeManager->unlock($node, $code);
        }

        return new RedirectResponse($this->container->get('router')->generate('claro_resource_open_short', ['node' => $node->getId()]));
    }

    private function isUsurpatingWorkspaceRole(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole()) {
                return true;
            }
        }

        return false;
    }

    private function mergeSearchedResources(array $resources, array $taggedResourceNodes)
    {
        $resourcesIds = array_column($resources, 'id');

        foreach ($taggedResourceNodes as $node) {
            if (!in_array($node->getId(), $resourcesIds)) {
                $taggedResource = [
                    'id' => $node->getId(),
                    'name' => $node->getName(),
                    'path' => $node->getPath(),
                    'creator_username' => $node->getCreator()->getUsername(),
                    'creator_id' => $node->getCreator()->getId(),
                    'type' => $node->getResourceType()->getName(),
                    'mime_type' => $node->getMimeType(),
                    'index_dir' => $node->getIndex(),
                    'creation_date' => $node->getCreationDate(),
                    'modification_date' => $node->getModificationDate(),
                    'published' => $node->isPublished(),
                    'accessible_from' => $node->getAccessibleFrom(),
                    'accessible_until' => $node->getAccessibleUntil(),
                ];
                $parent = $node->getParent();
                $icon = $node->getIcon();
                $taggedResource['parent_id'] = empty($parent) ? null : $parent->getId();
                $taggedResource['large_icon'] = empty($icon) ? null : $icon->getRelativeUrl();
                $resources[] = $taggedResource;
            }
        }

        return $resources;
    }
}
