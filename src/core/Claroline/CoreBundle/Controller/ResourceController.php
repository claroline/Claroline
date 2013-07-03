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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Event\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourceController extends Controller
{
    const THUMB_PER_PAGE = 12;

    private $formFactory;
    private $ed;
    private $sc;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("claroline.form.factory"),
     *     "ed"              = @DI\Inject("event_dispatcher"),
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request")
     * })
     */
    public function __construct
    (
        FormFactory $formFactory,
        EventDispatcher $ed,
        SecurityContext $sc,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        Translator $translator,
        Request $request
    )
    {
        $this->formFactory = $formFactory;
        $this->ed = $ed;
        $this->sc = $sc;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $request;
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
        $eventName = 'create_form_'.$resourceType;
        $event = new CreateFormResourceEvent();
        $this->ed->dispatch($eventName, $event);

        if ($event->getResponseContent() === "") {
            throw new \Exception(
                "Event '{$eventName}' didn't receive any response."
            );
        }

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
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
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
        $eventName = 'create_'.$resourceType;
        $event = new CreateResourceEvent($resourceType);
        $this->ed->dispatch($eventName, $event);

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
        } else {
            if ($event->getErrorFormContent() != null) {
                return new Response(setContent($event->getErrorFormContent()));
            } else {
                throw new \Exception('creation failed');
            }
        }
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
     * @param AbstractResource $resource    the resource
     * @param string          $resourceType the resource type
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
        $event = new OpenResourceEvent($resource);
        $eventName = 'open_'.$resourceType;
        $this->ed->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Open event '{$eventName}' didn't return any Response."
            );
        }

        $this->ed->dispatch('log', new LogResourceReadEvent($resource));

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
     * @param $newParentId
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
     * @param string  $resourceType the resource type
     * @param string  $action       the action
     * @param integer $resource     the resource
     *
     * @throws \Exception
     * @return Response
     */
    public function customAction($resourceType, $action, AbstractResource $resource)
    {
        $eventName = $action . '_' . $resourceType;
        //$collection = new ResourceCollection(array($resource));

        $event = new CustomActionResourceEvent($resource);
        $this->ed->dispatch($eventName, $event);

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
     *     "/export",
     *     name="claro_resource_export",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction(array $resources)
    {
        $collection = new ResourceCollection($resources);
        $this->checkAccess('EXPORT', $collection);
        $file = $this->get('claroline.resource.exporter')->exportResources($resources);
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
     * @param integer $directoryId the directory id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception if the id doesnt't match any existing directory
     */
    public function openDirectoryAction(User $user, Directory $directory = null)
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

            if ($user === $directory->getCreator() || $this->sc->isGranted('ROLE_ADMIN')) {
                $canChangePosition = true;
            }

            $path = $this->resourceManager->getAncestors($directory);
            $resources = $this->resourceManager->getChildren($directory, $currentRoles);
            $creatableTypes = $this->rightsManager->getCreatableTypes($currentRoles, $directory);
            $this->ed->dispatch('log', new LogResourceReadEvent($directory));
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
     * @param integer $resourceDestinationId the new parent id.
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
     * @param integer $directoryId The id of the directory from which the search was started
     *
     * @throws \Exception
     * @return Response
     */
    public function filterAction(Directory $directory)
    {
        $criteria = $this->resourceManager->buildSearchArray($this->request->query->all());
        $criteria['roots'] = isset($criteria['roots']) ? $criteria['roots'] : array();
        $path = $this->resourceManager->getAncestors($directory);
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
     *
     * Creates (one or several) shortcuts.
     * Takes an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param $newParentId the shortcut parent id
     *
     * @return Response
     */
    public function createShortcutAction($newParentId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $ids = $this->container->get('request')->query->get('ids', array());
        $parent = $repo->find($newParentId);

        foreach ($ids as $resourceId) {
            $resource = $repo->find($resourceId);
            $creator = $this->get('security.context')->getToken()->getUser();
            $shortcut = $this->get('claroline.manager.resource_manager')->makeShortcut($resource, $parent, $creator);
            $em->flush();
            $em->refresh($parent);

            $links[] = $this->get('claroline.resource.converter')->toArray(
                $shortcut,
                $this->get('security.context')->getToken()
            );
        }

        $response = new Response(json_encode($links));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @todo move that function elsewhere.
     * @EXT\Route(
     *     "/search/role/code/{code}",
     *     name="claro_resource_find_role_by_code",
     *     options={"expose"=true}
     * )
     */
    public function findRoleByWorkspaceCodeAction($code)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspaceCodeTag($code);
        $arWorkspace = array();

        foreach ($roles as $role) {
            $arWorkspace[$role->getWorkspace()->getCode()][$role->getName()] = array(
                'name' => $role->getName(),
                'translation_key' => $role->getTranslationKey(),
                'id' => $role->getId(),
                'workspace' => $role->getWorkspace()->getName()
            );
        }

        return new JsonResponse($arWorkspace);
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Resource:breadcrumbs.html.twig")
     */
    public function renderBreadcrumbsAction($resourceId, $workspaceId, $_breadcrumbs)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($resourceId);

        $ancestors = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findAncestors($resource);

        $breadcrumbsAncestors = array();

        if (count($_breadcrumbs) > 0) {
            $breadcrumbsAncestors = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findResourcesByIds($_breadcrumbs);
            $breadcrumbsAncestors[] = $resource;

            if ($_breadcrumbs[0] != 0) {
                $rootId = $_breadcrumbs[0];
            } else {
                $rootId = $_breadcrumbs[1];
            }

            $workspaceId = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($rootId)->getWorkspace()->getId();
        }

        //this condition is wrong
        if (count($breadcrumbsAncestors) === 0) {

            $_breadcrumbs = array();

            foreach ($ancestors as $ancestor) {
                $_breadcrumbs[] = $ancestor['id'];
            }

            $breadcrumbsAncestors = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findResourcesByIds($_breadcrumbs);
        }

        if (!$this->get('claroline.manager.manager_resource')->areAncestorsDirectory($breadcrumbsAncestors)) {
            throw new \Exception('Breadcrumbs invalid');
        };

        return array(
            'ancestors' => $breadcrumbsAncestors,
            'workspaceId' => $workspaceId
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
     * @param type $nextId
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function insertBefore(AbstractResource $resource, AbstractResource $next, User $user)
    {
        if ($user !== $resource->getParent()->getCreator() && !$this->sc->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->resourceManager->insertBefore($resource, $next);

        return new Response('success', 204);
    }

    private function getResource($resource)
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