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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\TransferManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ResourceOldController.
 *
 * @todo restore used before remove (eg. the action about lock / unlock)
 */
class ResourceOldController extends Controller
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
    private $userManager;
    private $eventDispatcher;
    /** @var EventManager */
    private $eventManager;

    /**
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"         = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"       = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"          = @DI\Inject("translator"),
     *     "requestStack"        = @DI\Inject("request_stack"),
     *     "dispatcher"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "templating"          = @DI\Inject("templating"),
     *     "logManager"          = @DI\Inject("claroline.log.manager"),
     *     "fileManager"         = @DI\Inject("claroline.manager.file_manager"),
     *     "transferManager"     = @DI\Inject("claroline.manager.transfer_manager"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "eventManager"        = @DI\Inject("claroline.event.manager")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        StrictDispatcher $dispatcher,
        MaskManager $maskManager,
        TwigEngine $templating,
        LogManager $logManager,
        FileManager $fileManager,
        TransferManager $transferManager,
        FormFactory $formFactory,
        UserManager $userManager,
        EventDispatcherInterface $eventDispatcher,
        EventManager $eventManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->maskManager = $maskManager;
        $this->templating = $templating;
        $this->logManager = $logManager;
        $this->fileManager = $fileManager;
        $this->transferManager = $transferManager;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventManager = $eventManager;
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
            OpenResourceEvent::class,
            [$this->resourceManager->getResourceFromNode($node), $isIframe]
        );
        $this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$node]);
        $this->dispatcher->dispatch('log', 'Log\LogWorkspaceEnter', [$node->getWorkspace()]);

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *     "/logs/{node}",
     *     name="claro_resource_logs",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:resource/logs:list.html.twig")
     *
     * Shows resource logs list
     *
     * @param ResourceNode $node the resource
     *
     * @return array
     *
     * @throws \Exception
     */
    public function logListAction(ResourceNode $node)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);

        return [
            'workspace' => $node->getWorkspace(),
            '_resource' => $resource,
            'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
        ];
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:resource:breadcrumbs.html.twig")
     *
     * @param ResourceNode $node
     * @param int[]        $_breadcrumbs
     *
     * @return array
     *
     * @throws \Exception
     */
    public function renderBreadcrumbsAction(ResourceNode $node, $_breadcrumbs)
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
     * @EXT\Route(
     *     "/custom/{action}/{node}",
     *     name="claro_resource_custom_action",
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
        $menuAction = $this->maskManager->getMenuFromNameAndResourceType($action, $type);

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
            $decoder = $menuAction->getDecoder();
            $this->checkAccess($decoder, $collection);
            $eventName = $action.'_'.$type->getName();
        }
        $event = $this->dispatcher->dispatch(
            $eventName,
            'CustomActionResource',
            [$this->resourceManager->getResourceFromNode($node)]
        );

        return $event->getResponse();
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
}
