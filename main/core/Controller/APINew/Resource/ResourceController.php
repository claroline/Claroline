<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route("/resource", options={"expose"=true})
 */
class ResourceController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ResourceNodeSerializer */
    private $serializer;

    /** @var ResourceManager */
    private $resourceManager;

    /**
     * ResourceController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "dispatcher"             = @DI\Inject("claroline.event.event_dispatcher"),
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $dispatcher
     * @param ResourceNodeSerializer        $resourceNodeSerializer
     * @param ResourceManager               $resourceManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $dispatcher,
        ResourceNodeSerializer $resourceNodeSerializer,
        ResourceManager $resourceManager)
    {
        $this->authorization = $authorization;
        $this->dispatcher = $dispatcher;
        $this->serializer = $resourceNodeSerializer;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Loads a resource.
     *
     * Gets all data required in order to play the resource.
     * NB. In the near future, it will replace the `openAction`.
     *
     * @EXT\Route("/{type}/{node}", name="claro_resource_load")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $node
     * @param string       $type
     *
     * @return JsonResponse
     */
    public function loadAction(ResourceNode $node, $type)
    {
        // checks the current user can open the resource
        $this->checkAccess('OPEN', new ResourceCollection([$node]));

        // it's a shortcut : checks if User can open the target
        // IMO, it seems strange the User can open the shortcut but may not open the target
        $node = $this->resourceManager->getRealTarget($node);
        $this->checkAccess('OPEN', new ResourceCollection([$node]));

        /** @var LoadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            'load_'.$type,
            'Resource\LoadResource',
            [$this->resourceManager->getResourceFromNode($node)]
        );

        // maybe use a specific log ?
        $this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$node]);

        return new JsonResponse(
            array_merge([
                'resourceNode' => $this->serializer->serialize($node),
                'evaluation' => null, // todo flag evaluated resource types and auto load Evaluation if any
            ], $event->getAdditionalData())
        );
    }

    /**
     * Checks current user access rights to a collection of Resources.
     *
     * @param $permission
     * @param ResourceCollection $collection
     */
    private function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }
    }
}
