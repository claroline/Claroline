<?php

namespace Claroline\AppBundle\Controller\Component;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Tool\ToolInterface;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the different application contexts (public, desktop, administration, ...).
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
    public function openAction(string $context, string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $contextObject = $contextHandler->getObject($contextId);

        $contextRoles = $contextHandler->getRoles($this->tokenStorage->getToken(), $contextObject);
        $isImpersonated = $contextHandler->isImpersonated($this->tokenStorage->getToken(), $contextObject);
        $isManager = $contextHandler->isManager($this->tokenStorage->getToken(), $contextObject);
        $accessErrors = $contextHandler->getAccessErrors($this->tokenStorage->getToken(), $contextObject);

        // $this->authorization->isGranted('OPEN', $contextObject);

        if (empty($accessErrors) || $isManager) {
            $openEvent = new OpenContextEvent($context, $contextObject);
            $this->eventDispatcher->dispatch($openEvent, ContextEvents::OPEN);

            return new JsonResponse(array_merge($openEvent->getResponse() ?? [], [
                'data' => $contextObject ? $this->serializer->serialize($contextObject) : null, // maybe only expose minimal ?

                'managed' => $isManager,
                'impersonated' => $isImpersonated,
                'roles' => array_values(array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextRoles)),
                'accessErrors' => $accessErrors,

                // get all enabled tools for the context, even those inaccessible to the current user
                // this will allow the ui to know if a user try to access a closed tool or a non-existent one.
                'tools' => array_map(function (OrderedTool $orderedTool) {
                    return $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextHandler->getTools($contextObject)),
            ], $contextHandler->getAdditionalData($contextObject)));
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
     * Configures a context.
     *
     * @Route("", name="claro_context_configure", methods={"PUT"})
     */
    public function configureAction(string $context, string $contextId = null): JsonResponse
    {
        // $this->authorization->isGranted('ADMINISTRATE', $contextObject);

        return new JsonResponse();
    }

    /**
     * Gets the list of available tools (all tools implemented, not only the enabled ones in the context).
     *
     * @Route("/tools", name="claro_context_get_available_tools", methods={"GET"})
     */
    public function getAvailableToolsAction(string $context, string $contextId = null): JsonResponse
    {
        // $this->authorization->isGranted('ADMINISTRATE', $contextObject);

        $contextHandler = $this->contextProvider->getContext($context);
        $contextSubject = $contextHandler->getObject($contextId);

        $tools = $contextHandler->getAvailableTools($contextSubject);

        return new JsonResponse(array_map(function (ToolInterface $tool) use ($context, $contextSubject) {
            return [
                'icon' => $tool::getIcon(),
                'name' => $tool::getName(),
                'required' => $tool->isRequired($context, $contextSubject)
            ];
        }, $tools));
    }
}
