<?php

namespace Claroline\CoreBundle\Controller;

use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Event\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourceController extends Controller
{
    const THUMB_PER_PAGE = 12;

    private $formFactory;
    private $sc;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;
    private $dispatcher;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("claroline.form.factory"),
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct
    (
        FormFactory $formFactory,
        SecurityContext $sc,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        Translator $translator,
        Request $request,
        StrictDispatcher $dispatcher
    )
    {
        $this->formFactory = $formFactory;
        $this->sc = $sc;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
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
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "parentId", "strictId" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Creates a resource.
     *
     * @param string           $resourceType the resource type
     * @param AbstractResource $parent       the parent
     * @param User             $user         the user
     *
     * @throws \Exception
     * @return Response
     */
    public function createAction($resourceType, AbstractResource $parent, User $user)
    {
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => $resourceType));
        $this->checkAccess('CREATE', $collection);
        $event = $this->dispatcher->dispatch('create_'.$resourceType, 'CreateResource', array($resourceType));

        if (count($event->getResources()) > 0) {
            $resourcesArray = array();

            foreach ($event->getResources() as $resource) {
                $createdResource = $this->resourceManager->create(
                    $resource,
                    $this->resourceManager->getResourceTypeByName($resourceType),
                    $user,
                    $parent->getWorkspace(),
                    $parent
                );

                $resourcesArray[] = $this->resourceManager->toArray($createdResource);
            }

            return new JsonResponse($resourcesArray);
        }

        return new Response(setContent($event->getErrorFormContent()));
    }

    /**
     * @EXT\Route(
     *     "/open/{resourceType}/{resourceId}",
     *     name="claro_resource_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     *
     * Opens a resource.
     *
     * @param AbstractResource $resource     the resource
     * @param string           $resourceType the resource type
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function openAction(AbstractResource $resource, $resourceType)
    {
        $collection = new ResourceCollection(array($resource));
        //If it's a link, the resource will be its target.
        $resource = $this->getResource($resource);
        $this->checkAccess('OPEN', $collection);
        $event = $this->dispatcher->dispatch('open_'.$resourceType, 'OpenResource', array($resource));
        $this->dispatcher->dispatch('log', 'Log\ResourceRead', array($resource));

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="claro_resource_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "resources",
     *     class="ClarolineCoreBundle:Resource\AbstractResource",
     *     options={"multipleIds" = true}
     * )
     *
     * Removes a many resources from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param array $resources
     *
     * @return Response
     */
    public function deleteAction(array $resources)
    {
        $collection = new ResourceCollection($resources);
        $this->checkAccess('DELETE', $collection);

        foreach ($collection->getResources() as $resource) {
            $this->resourceManager->delete($resource);
        }

        return new Response('Resource deleted', 204);
    }

    /**
     * @EXT\Route(
     *     "/move/{newParentId}",
     *     name="claro_resource_move",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "resources",
     *     class="ClarolineCoreBundle:Resource\AbstractResource",
     *     options={"multipleIds" = true}
     * )
     * @EXT\ParamConverter(
     *      "newParent",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "newParentId", "strictId" = true}
     * )
     *
     * Moves many resource (changes their parents). This function takes an array
     * of parameters which are the ids of the moved resources
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param AbstractResource $newParent
     * @param array            $resources
     *
     * @throws \RuntimeException
     * @return Response
     */
    public function moveAction(AbstractResource $newParent, array $resources)
    {
        $collection = new ResourceCollection($resources);
        $collection->addAttribute('parent', $newParent);
        $this->checkAccess('MOVE', $collection);

        foreach ($resources as $resource) {
            try {
                $movedResource = $this->resourceManager->move($resource, $newParent);
                $movedResources[] = $this->resourceManager->toArray($movedResource);
            } catch (\Gedmo\Exception\UnexpectedValueException $e) {
                throw new \RuntimeException('Cannot move a resource into itself');
            }
        }

        return new JsonResponse($movedResources);
    }

    /**
     * @EXT\Route(
     *     "/custom/{resourceType}/{action}/{resourceId}",
     *     name="claro_resource_custom",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     *
     * Handles any custom action (i.e. not defined in this controller) on a
     * resource of a given type.
     *
     * @param string           $resourceType the resource type
     * @param string           $action       the action
     * @param AbstractResource $resource     the resource
     *
     * @throws \Exception
     * @return Response
     */
    public function customAction($resourceType, $action, AbstractResource $resource)
    {
        $eventName = $action . '_' . $resourceType;
        //$collection = new ResourceCollection(array($resource));

        $event = new CustomActionResourceEvent($resource);
        //$this->ed->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Custom event '{$eventName}' didn't return any Response."
            );
        }

        //TODO waiting for define CustomActions
        // $logevent = new ResourceLogEvent($ri, $action);
        // $this->get('event_dispatcher')->dispatch('log_resource', $logevent);

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/download",
     *     name="claro_resource_download",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "resources",
     *     class="ClarolineCoreBundle:Resource\AbstractResource",
     *     options={"multipleIds" = true}
     * )
     *
     * This function takes an array of parameters. Theses parameters are the ids
     * of the resources which are going to be downloaded
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param array $resources
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(array $resources)
    {
        $collection = new ResourceCollection($resources);
        $this->checkAccess('EXPORT', $collection);
        $file = $this->resourceManager->download($resources);
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
     *     "directory/{directoryId}",
     *     name="claro_resource_directory",
     *     options={"expose"=true},
     *     defaults={"directoryId"=0}
     * )
     * @EXT\ParamConverter(
     *      "directory",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "directoryId", "strictId" = true}
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
     * @param Directory $directory the directory
     * @param User      $user      the user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception if the id doesnt't match any existing directory
     */
    public function openDirectoryAction(User $user, AbstractResource $directory = null)
    {
        $path = array();
        $creatableTypes = array();
        $currentRoles = $this->roleManager->getStringRolesFromCurrentUser();
        $canChangePosition = false;

        if ($directory === null) {
            $resources = $this->resourceManager->getRoots($user);
            $isRoot = true;
            $workspaceId = 0;
        } else {
            $isRoot = false;
            $workspaceId = $directory->getWorkspace()->getId();
            $directory = $this->getResource($directory);

            if ($user === $directory->getCreator() || $this->sc->isGranted('ROLE_ADMIN')) {
                $canChangePosition = true;
            }

            $path = $this->resourceManager->getAncestors($directory);
            $resources = $this->resourceManager->getChildren($directory, $currentRoles);
            $creatableTypes = $this->rightsManager->getCreatableTypes($currentRoles, $directory);
            $this->dispatcher->dispatch('log', 'Log\ResourceRead', array($directory));
        }

        return new JsonResponse(
            array(
                'path' => $path,
                'creatableTypes' => $creatableTypes,
                'resources' => $resources,
                'canChangePosition' => $canChangePosition,
                'workspace_id' => $workspaceId,
                'is_root' => $isRoot
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/copy/{resourceDestinationId}",
     *     name="claro_resource_copy",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "parent",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceDestinationId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "resources",
     *     class="ClarolineCoreBundle:Resource\AbstractResource",
     *     options={"multipleIds" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Adds multiple resource resource to a workspace.
     * Needs an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param AbstractResource $parent
     * @param array            $resources
     * @param User             $user
     *
     * @return Response
     */
    public function copyAction(AbstractResource $parent, array $resources, User $user)
    {
        $newNodes = array();
        $collection = new ResourceCollection($resources);
        $collection->addAttribute('parent', $parent);
        $this->checkAccess('COPY', $collection);

        foreach ($resources as $resource) {
            $newNodes[] = $this->resourceManager->toArray($this->resourceManager->copy($resource, $parent, $user));
        }

        return new JsonResponse($newNodes);
    }

    /**
     * @EXT\Route(
     *     "/filter/{directoryId}",
     *     name="claro_resource_filter",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "directory",
     *      class="ClarolineCoreBundle:Resource\Directory",
     *      options={"id" = "directoryId", "strictId" = true}
     * )
     *
     * Returns a json representation of a resource search result.
     *
     * @param Directory $directory The id of the directory from which the search was started
     *
     * @throws \Exception
     * @return Response
     */
    public function filterAction(Directory $directory = null)
    {
        $criteria = $this->resourceManager->buildSearchArray($this->request->query->all());
        $criteria['roots'] = isset($criteria['roots']) ? $criteria['roots'] : array();
        $path = $directory ? $this->resourceManager->getAncestors($directory): array();
        $userRoles = $this->roleManager->getStringRolesFromCurrentUser();
        $resources = $this->resourceManager->getByCriteria($criteria, $userRoles, true);

        return new JsonResponse(array('resources' => $resources, 'path' => $path));
    }

    /**
     * @EXT\Route(
     *     "/shortcut/{newParentId}/create",
     *     name="claro_resource_create_shortcut",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("creator", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "parent",
     *      class="ClarolineCoreBundle:Resource\Directory",
     *      options={"id" = "newParentId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "resources",
     *     class="ClarolineCoreBundle:Resource\AbstractResource",
     *     options={"multipleIds" = true}
     * )
     *
     * Creates (one or several) shortcuts.
     * Takes an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param Directory $parent    the new parent
     * @param User      $user      the shortcut creator
     * @param array     $resources the resources going to be linked
     *
     * @return Response
     */
    public function createShortcutAction(Directory $parent, User $creator, array $resources)
    {
        foreach ($resources as $resource) {
            $shortcut = $this->resourceManager->makeShortcut($resource, $parent, $creator, new ResourceShortcut());
            $links[] = $this->resourceManager->toArray($shortcut);
        }

        return new JsonResponse($links);
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Resource:breadcrumbs.html.twig")
     */
    public function renderBreadcrumbsAction(AbstractResource $resource, AbstractWorkspace $workspace, $_breadcrumbs)
    {
        $breadcrumbsAncestors = array();

        if (count($_breadcrumbs) > 0) {
            $breadcrumbsAncestors = $this->resourceManager->getByIds($_breadcrumbs);
            $breadcrumbsAncestors[] = $resource;
            $root = $breadcrumbsAncestors[0];
            $workspace = $root->getWorkspace();
        }

        //this condition is wrong
        if (count($breadcrumbsAncestors) === 0) {
            $_breadcrumbs = array();
            $ancestors = $this->resourceManager->getAncestors($resource);

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
     *     "/sort/{resourceId}/next/{nextId}",
     *     name="claro_resource_insert_before",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "next",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "nextId", "strictId" = true}
     * )
     *
     * @param AbstractResource $resource
     * @param AbstractResource $next
     * @param User             $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function insertBefore(AbstractResource $resource, User $user, AbstractResource $next = null)
    {
        if ($user !== $resource->getParent()->getCreator() && !$this->sc->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->insertBefore($resource, $next);

        return new Response('success', 204);
    }

    private function getResource(AbstractResource $resource)
    {
        if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $resource = $resource->getResource();
        }

        return $resource;
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
     * @param string $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->sc->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}