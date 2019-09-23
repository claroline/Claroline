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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\Exception\ResourceNotFoundException;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\Resource\ResourceRestrictionsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Manages platform resources.
 * ATTENTION. be careful if you change routes order.
 *
 * @EXT\Route("/resources", options={"expose"=true})
 */
class ResourceController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var EngineInterface */
    private $templating;

    /** @var Utilities */
    private $security;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceManager */
    private $manager;

    /** @var ResourceActionManager */
    private $actionManager;

    /** @var ResourceRestrictionsManager */
    private $restrictionsManager;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceRightsRepository */
    private $rightsRepo;

    /** @var EventManager */
    private $eventManager;

    /** @var ResourceEvaluationManager */
    private $resourceEvaluationManager;

    /**
     * ResourceController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"              = @DI\Inject("security.token_storage"),
     *     "templating"                = @DI\Inject("templating"),
     *     "finder"                    = @DI\Inject("claroline.api.finder"),
     *     "security"                  = @DI\Inject("claroline.security.utilities"),
     *     "serializer"                = @DI\Inject("claroline.api.serializer"),
     *     "manager"                   = @DI\Inject("claroline.manager.resource_manager"),
     *     "actionManager"             = @DI\Inject("claroline.manager.resource_action"),
     *     "restrictionsManager"       = @DI\Inject("claroline.manager.resource_restrictions"),
     *     "om"                        = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization"             = @DI\Inject("security.authorization_checker"),
     *     "eventManager"              = @DI\Inject("claroline.event.manager"),
     *     "resourceEvaluationManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param EngineInterface               $templating
     * @param Utilities                     $security
     * @param SerializerProvider            $serializer
     * @param ResourceManager               $manager
     * @param ResourceActionManager         $actionManager
     * @param ResourceRestrictionsManager   $restrictionsManager
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param EventManager                  $eventManager
     * @param ResourceEvaluationManager     $resourceEvaluationManager
     * @param FinderProvider                $finder
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        EngineInterface $templating,
        Utilities $security,
        SerializerProvider $serializer,
        ResourceManager $manager,
        ResourceActionManager $actionManager,
        ResourceEvaluationManager $resourceEvaluationManager,
        ResourceRestrictionsManager $restrictionsManager,
        ObjectManager $om,
        EventManager $eventManager,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->actionManager = $actionManager;
        $this->restrictionsManager = $restrictionsManager;
        $this->om = $om;
        $this->rightsRepo = $om->getRepository(ResourceRights::class);
        $this->authorization = $authorization;
        $this->eventManager = $eventManager;
        $this->finder = $finder;
        $this->resourceEvaluationManager = $resourceEvaluationManager;
    }

    /**
     * Opens a resource.
     *
     * @EXT\Route("/load/{id}", name="claro_resource_load")
     * @EXT\Route("/load/{id}/embedded/{embedded}", name="claro_resource_load_embedded")
     * @EXT\Method("GET")
     *
     * @param int|string $id       - the id of the target node (we don't use ParamConverter to support ID and UUID)
     * @param int        $embedded
     *
     * @return JsonResponse
     */
    public function openAction($id, $embedded = 0)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->om->find(ResourceNode::class, $id);
        if (!$resourceNode) {
            return new JsonResponse(['resource_not_found'], 404);
        }

        // gets the current user roles to check access restrictions
        $userRoles = $this->security->getRoles($this->tokenStorage->getToken());
        $accessErrors = $this->restrictionsManager->getErrors($resourceNode, $userRoles);

        if (empty($accessErrors) || $this->manager->isManager($resourceNode)) {
            try {
                $loaded = $this->manager->load($resourceNode, intval($embedded) ? true : false);
                //I have no idea if it is correct to do this
                if ('anon.' !== $this->tokenStorage->getToken()->getUser()) {
                    $this->resourceEvaluationManager->createResourceEvaluation($resourceNode, $this->tokenStorage->getToken()->getUser(), null, [
                        'status' => ResourceEvaluation::STATUS_PARTICIPATED,
                    ]);
                }
            } catch (ResourceNotFoundException $e) {
                // Not a 404 because we should not have ResourceNode without a linked AbstractResource
                return new JsonResponse(['resource_not_found'], 500);
            }

            return new JsonResponse(
                array_merge([
                    // append access restrictions to the loaded node if any
                    // to let the manager knows that other users can not enter the resource
                    'accessErrors' => $accessErrors,
                ], $loaded)
            );
        }

        return new JsonResponse($accessErrors, 403);
    }

    /**
     * Embeds a resource inside a rich text content.
     *
     * @EXT\Route("/embed/{id}", name="claro_resource_embed_short")
     * @EXT\Route("/embed/{type}/{id}", name="claro_resource_embed")
     *
     * @param ResourceNode $resourceNode
     *
     * @return Response
     */
    public function embedAction(ResourceNode $resourceNode)
    {
        $mimeType = explode('/', $resourceNode->getMimeType());

        $view = 'default';
        if ($mimeType[0] && in_array($mimeType[0], ['video', 'audio', 'image'])) {
            $view = $mimeType[0];
        }

        return new Response(
            $this->templating->render("ClarolineCoreBundle:resource:embed/{$view}.html.twig", [
                'resource' => $this->manager->getResourceFromNode($resourceNode),
            ])
        );
    }

    /**
     * Downloads a list of Resources.
     *
     * @EXT\Route(
     *     "/download",
     *     name="claro_resource_download",
     *     defaults ={"forceArchive"=false}
     * )
     * @EXT\Route(
     *     "/download/{forceArchive}",
     *     name="claro_resource_download",
     *     requirements={"forceArchive" = "^(true|false|0|1)$"},
     * )
     *
     * @param bool    $forceArchive
     * @param Request $request
     *
     * @return JsonResponse|BinaryFileResponse
     */
    public function downloadAction($forceArchive = false, Request $request)
    {
        $ids = $request->query->get('ids');
        $nodes = $this->om->findList(ResourceNode::class, 'uuid', $ids);

        $collection = new ResourceCollection($nodes);

        if (!$this->authorization->isGranted('EXPORT', $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }

        $data = $this->manager->download($nodes, $forceArchive);

        $file = $data['file'] ?: tempnam('tmp', 'tmp');
        $fileName = $data['name'];

        if (!file_exists($file)) {
            return new JsonResponse(['file_not_found'], 500);
        }

        if ($fileName) {
            $fileName = str_replace('/', '_', $fileName);
            $fileName = str_replace('\\', '_', $fileName);
        }

        $response = new BinaryFileResponse($file, 200, ['Content-Disposition' => "attachment; filename={$fileName}"]);

        return $response;
    }

    /**
     * Submit access code.
     *
     * @EXT\Route("/unlock/{id}", name="claro_resource_unlock")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function unlockAction(ResourceNode $resourceNode, Request $request)
    {
        $this->restrictionsManager->unlock($resourceNode, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    /**
     * Executes an action on a collection of resources.
     *
     * @EXT\Route("/collection/{action}", name="claro_resource_collection_action")
     *
     * @param string  $action
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function executeCollectionAction($action, Request $request)
    {
        $ids = $request->query->get('ids');

        /** @var ResourceNode[] $resourceNodes */
        $resourceNodes = $this->om->findList(ResourceNode::class, 'uuid', $ids);
        $responses = [];

        // read request and get user query
        $parameters = $request->query->all();
        $content = null;

        if (!empty($request->getContent())) {
            $content = json_decode($request->getContent(), true);
        }
        $files = $request->files->all();

        $this->om->startFlushSuite();

        foreach ($resourceNodes as $resourceNode) {
            // check the requested action exists
            if (!$this->actionManager->support($resourceNode, $action, $request->getMethod())) {
                // undefined action
                throw new NotFoundHttpException(
                    sprintf('The action %s with method [%s] does not exist for resource type %s.', $action, $request->getMethod(), $resourceNode->getResourceType()->getName())
                );
            }

            // check current user rights
            $this->checkAccess($this->actionManager->get($resourceNode, $action), [$resourceNode]);

            // dispatch action event
            $responses[] = $this->actionManager->execute($resourceNode, $action, $parameters, $content, $files);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Response $response) {
            return json_decode($response->getContent(), true);
        }, $responses));
    }

    /**
     * Executes an action on one resource.
     *
     * @EXT\Route("/{action}/{id}", name="claro_resource_action_short")
     * @EXT\Route("/{type}/{action}/{id}", name="claro_resource_action")
     *
     * @param string       $action
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function executeAction($action, ResourceNode $resourceNode, Request $request)
    {
        // check the requested action exists
        if (!$this->actionManager->support($resourceNode, $action, $request->getMethod())) {
            // undefined action
            throw new NotFoundHttpException(
                sprintf('The action %s with method [%s] does not exist for resource type %s.', $action, $request->getMethod(), $resourceNode->getResourceType()->getName())
            );
        }

        // check current user rights
        $this->checkAccess($this->actionManager->get($resourceNode, $action), [$resourceNode]);

        // read request and get user query
        $parameters = $request->query->all();

        $content = null;

        if (!empty($request->getContent())) {
            $content = json_decode($request->getContent(), true);
        }
        $files = $request->files->all();

        // dispatch action event
        return $this->actionManager->execute($resourceNode, $action, $parameters, $content, $files);
    }

    /**
     * Shows resource logs list.
     *
     * @todo use standard resource action system.
     *
     * @EXT\Route("/logs/{node}", name="claro_resource_logs")
     * @EXT\Template("ClarolineCoreBundle:resource/logs:list.html.twig")
     *
     * @param ResourceNode $node the resource
     *
     * @return array
     */
    public function logListAction(ResourceNode $node)
    {
        $resource = $this->manager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        if (!$this->authorization->isGranted('ADMINISTRATE', $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }

        return [
            'workspace' => $node->getWorkspace(),
            '_resource' => $resource,
            'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
        ];
    }

    /**
     * Gets a resource node.
     *
     * @EXT\Route("/{slug}", name="claro_resource_get")
     * @EXT\Method("GET")
     *
     * @param string $slug - the slug or UUID of the target node (we don't use ParamConverter to support slug and UUID)
     *
     * @return JsonResponse
     *
     * @throws ResourceNotFoundException
     */
    public function getAction($slug)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->finder->get(ResourceNode::class)->findOneBy(['uuid_or_slug' => $slug]);

        if (!$resourceNode) {
            throw new ResourceNotFoundException('Resource not found');
        }

        return new JsonResponse(
            $this->serializer->serialize($resourceNode, [Options::SERIALIZE_MINIMAL])
        );
    }

    /**
     * Checks the current user can execute the action on the requested nodes.
     *
     * @param MenuAction $action
     * @param array      $resourceNodes
     * @param array      $attributes
     */
    private function checkAccess(MenuAction $action, array $resourceNodes, array $attributes = [])
    {
        $collection = new ResourceCollection($resourceNodes);
        $collection->setAttributes($attributes);

        if (!$this->actionManager->hasPermission($action, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }
    }
}
