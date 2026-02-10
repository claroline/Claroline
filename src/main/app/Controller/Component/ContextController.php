<?php

namespace Claroline\AppBundle\Controller\Component;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Tool\ToolInterface;
use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Event\Context\OpenContextEvent;
use Claroline\AppBundle\Manager\Component\ContextManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the different application contexts (public, desktop, administration, ...).
 */
#[Route(path: '/context/{context}/{contextId}')]
#[Route(path: '/context/{context}')]
class ContextController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ContextManager $contextManager,
        private readonly ContextProvider $contextProvider,
        private readonly ToolProvider $toolProvider
    ) {
    }

    /**
     * Opens a context.
     */
    #[Route(path: '', name: 'claro_context_open', methods: ['GET'])]
    public function openAction(string $context, string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $contextHandler = $this->contextProvider->getContext($context);
            $contextSubject = $contextHandler->getSubject($contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$contextHandler->isAvailable()) {
            throw new NotFoundHttpException();
        }

        $contextRoles = $contextHandler->getRoles($this->tokenStorage->getToken(), $contextSubject);
        $isImpersonated = $contextHandler->isImpersonated($this->tokenStorage->getToken(), $contextSubject);

        if ($contextHandler->isGranted('OPEN', $contextSubject)) {
            $openEvent = new OpenContextEvent($context, $contextSubject);
            $this->eventDispatcher->dispatch($openEvent, ContextEvents::OPEN);

            $contextOrganizations = $contextHandler->getOrganizations($this->tokenStorage->getToken(), $contextSubject);
            $contextTools = array_values(array_filter($this->toolProvider->getEnabledTools($context, $contextSubject), function (OrderedTool $tool) {
                return $this->authorization->isGranted('OPEN', $tool);
            }));

            return new JsonResponse(array_merge($openEvent->getResponse() ?? [], [
                'data' => $contextSubject ? $this->serializer->serialize($contextSubject) : null,

                'impersonated' => $isImpersonated,
                'roles' => array_values(array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextRoles)),
                'organizations' => array_map(function (Organization $organization) {
                    return $this->serializer->serialize($organization, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextOrganizations),

                'tools' => array_map(function (OrderedTool $orderedTool) use ($context, $contextSubject) {
                    $serializedTool = $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);

                    return array_merge([], $serializedTool, [
                        'status' => $this->toolProvider->getStatus($orderedTool->getName(), $context, $contextSubject),
                    ]);
                }, $contextTools),
            ], $contextHandler->getAdditionalData($contextSubject)));
        }

        // return the details of access errors to display it to users
        $accessError = $contextHandler->getAccessError($this->tokenStorage->getToken(), $contextSubject);

        return new JsonResponse([
            'data' => $contextSubject ? $this->serializer->serialize($contextSubject, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'impersonated' => $isImpersonated,
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $contextRoles),
            'error' => $accessError,
        ], 403);
    }

    /**
     * Configures a context.
     */
    #[Route(path: '', name: 'claro_context_configure', methods: ['PUT'])]
    public function configureAction(Request $request, string $context, string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $data = $this->decodeRequest($request);

        $contextSubject = $this->contextManager->update($context, $contextId, $data);

        // reload tools
        $contextTools = $this->toolProvider->getEnabledTools($context, $contextSubject);

        // reopen context to get fresh data
        return new JsonResponse(array_merge([], [
            'data' => $contextSubject ? $this->serializer->serialize($contextSubject) : null,
            'tools' => array_map(function (OrderedTool $orderedTool) use ($context, $contextSubject) {
                $serializedTool = $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);

                return array_merge([], $serializedTool, [
                    'status' => $serializedTool['permissions']['open'] ? $this->toolProvider->getStatus($orderedTool->getName(), $context, $contextSubject) : null,
                ]);
            }, $contextTools),
        ], $contextHandler->getAdditionalData($contextSubject)));
    }

    /**
     * Gets the list of available tools (all tools implemented, not only the enabled ones in the context).
     */
    #[Route(path: '/tools', name: 'claro_context_get_available_tools', methods: ['GET'])]
    public function getAvailableToolsAction(string $context, string $contextId = null): JsonResponse
    {
        $contextHandler = $this->contextProvider->getContext($context);
        $contextSubject = $contextHandler->getSubject($contextId);

        if (!$contextHandler->isGranted('ADMINISTRATE', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $tools = $this->toolProvider->getAvailableTools($context, $contextSubject);

        return new JsonResponse(array_map(function (ToolInterface $tool) use ($context, $contextSubject) {
            return [
                'icon' => $tool::getIcon(),
                'name' => $tool::getName(),
                'required' => $tool->isRequired($context, $contextSubject),
            ];
        }, $tools));
    }
}
