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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\ResourceRestrictionsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/resources", options={"expose"=true})
 */
class ResourceController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

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

    /** @var ResourceLifecycleManager */
    private $lifecycleManager;

    /**
     * ResourceController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "security"            = @DI\Inject("claroline.security.utilities"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer"),
     *     "manager"             = @DI\Inject("claroline.manager.resource_manager"),
     *     "actionManager"       = @DI\Inject("claroline.manager.resource_action"),
     *     "restrictionsManager" = @DI\Inject("claroline.manager.resource_restrictions"),
     *     "lifecycleManager"    = @DI\Inject("claroline.manager.resource_lifecycle")
     * })
     *
     * @param TokenStorageInterface       $tokenStorage
     * @param Utilities                   $security
     * @param SerializerProvider          $serializer
     * @param ResourceManager             $manager
     * @param ResourceActionManager       $actionManager
     * @param ResourceRestrictionsManager $restrictionsManager
     * @param ResourceLifecycleManager    $lifecycleManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Utilities $security,
        SerializerProvider $serializer,
        ResourceManager $manager,
        ResourceActionManager $actionManager,
        ResourceRestrictionsManager $restrictionsManager,
        ResourceLifecycleManager $lifecycleManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->actionManager = $actionManager;
        $this->restrictionsManager = $restrictionsManager;
        $this->lifecycleManager = $lifecycleManager;
    }

    /**
     * Renders a resource application.
     *
     * @EXT\Route("/show/{id}", name="claro_resource_show_short")
     * @EXT\Route("/show/{type}/{id}", name="claro_resource_show")
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * @param ResourceNode $resourceNode
     *
     * @return array
     */
    public function showAction(ResourceNode $resourceNode)
    {
        return [
            'resourceNode' => $resourceNode,
        ];
    }

    /**
     * Gets a resource.
     *
     * @EXT\Route("/{id}", name="claro_resource_load_short")
     * @EXT\Route("/{type}/{id}", name="claro_resource_load")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function getAction(ResourceNode $resourceNode)
    {
        // gets the current user roles to check access restrictions
        $userRoles = $this->security->getRoles($this->tokenStorage->getToken());

        $accessErrors = $this->restrictionsManager->getErrors($resourceNode, $userRoles);
        if (empty($accessErrors) || $this->manager->isManager($resourceNode)) {
            $loaded = $this->manager->load($resourceNode);

            return new JsonResponse(
                array_merge([
                    // append access restrictions to the loaded node
                    // if any to let know the manager that other user can not enter the resource
                    'accessErrors' => $accessErrors,
                ], $loaded)
            );
        }

        return new JsonResponse($accessErrors, 403);
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

        // dispatch action event
        return $this->actionManager->execute($resourceNode, $action, $parameters, $content);
    }

    /**
     * Executes an action on a collection of resources.
     *
     * @param Request $request
     */
    public function executeCollectionAction(Request $request)
    {
        // TODO : implement
    }

    /**
     * Submit access code.
     *
     * @EXT\Route("/{id}/unlock", name="claro_resource_unlock")
     * @EXT\Method("POST")
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function unlockAction(ResourceNode $resourceNode, Request $request)
    {
        $this->restrictionsManager->unlock($resourceNode, json_decode($request->getContent(), true));

        return new JsonResponse(null, 204);
    }

    /**
     * Checks the current user can execute the action on the requested nodes.
     *
     * @param MenuAction $action
     * @param array      $resourceNodes
     */
    private function checkAccess(MenuAction $action, array $resourceNodes)
    {
        $collection = new ResourceCollection($resourceNodes);
        if (!$this->actionManager->hasPermission($action, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }
    }
}
