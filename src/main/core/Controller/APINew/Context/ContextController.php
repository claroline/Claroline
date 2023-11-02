<?php

namespace Claroline\CoreBundle\Controller\APINew\Context;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextInterface;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
use Claroline\CoreBundle\Event\Context\CloseContextEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the different application contexts (public, desktop, administration, ...)
 *
 * @Route("/context/{context}/{contextId}")
 * @Route("/context/{context}")
 */
class ContextController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SerializerProvider $serializer,
        private readonly ContextProvider $contextProvider
    ) {
    }

    /**
     * Opens a context.
     *
     * @Route("", name="claro_context_open", methods={"GET"})
     */
    public function openAction(string $context, ?string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            /** @var ContextInterface $contextHandler */
            $contextHandler = $this->contextProvider->getComponent($context);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $isImpersonated = $contextHandler->isImpersonated($contextId, $this->tokenStorage->getToken());
        $isManager = $contextHandler->isManager($contextId, $this->tokenStorage->getToken());
        $accessErrors = $contextHandler->getAccessErrors($contextId, $this->tokenStorage->getToken());

        $contextObject = $contextHandler->getObject($contextId);
        $contextRoles = $contextHandler->getRoles($contextId, $this->tokenStorage->getToken());

        if (empty($accessErrors) || $isManager) {
            $openEvent = new OpenContextEvent($context, $contextId);
            $this->eventDispatcher->dispatch($openEvent, ContextEvents::OPEN);

            return new JsonResponse(array_merge($openEvent->getResponse() ?? [], [
                'data' => $contextObject ? $this->serializer->serialize($contextObject) : null, // maybe only expose minimal ?

                'managed' => $isManager,
                'impersonated' => $isImpersonated,
                'roles' => array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextRoles),
                'accessErrors' => $accessErrors,

                // get all enabled tools for the context, even those inaccessible to the current user
                // this will allow the ui to know if a user try to access a closed tool or a non-existent one.
                'tools' => array_map(function (OrderedTool $orderedTool) {
                    return $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextHandler->getTools($contextId)),
                'shortcuts' => array_map(function (Shortcuts $shortcuts) {
                    return $this->serializer->serialize($shortcuts, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextHandler->getShortcuts($contextId)),
            ], $contextHandler->getAdditionalData($contextId)));
        }

        // return the details of access errors to display it to users
        return new JsonResponse([
            'data' => $contextObject ? $this->serializer->serialize($contextObject) : null, // maybe only expose minimal ?

            'managed' => false,
            'impersonated' => $isImpersonated,
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $contextRoles),
            'accessErrors' => $accessErrors,
        ], 403);
    }

    /**
     * Closes a context.
     *
     * @Route("/close", name="claro_context_close", methods={"PUT"})
     */
    public function closeAction(string $context, ?string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $this->contextProvider->getComponent($context);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $closeEvent = new CloseContextEvent($context, $contextId);
        $this->eventDispatcher->dispatch($closeEvent, ContextEvents::CLOSE);

        return new JsonResponse(null, 204);
    }
}
