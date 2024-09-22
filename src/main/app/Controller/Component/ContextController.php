<?php

namespace Claroline\AppBundle\Controller\Component;

use Exception;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Tool\ToolInterface;
use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
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
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);
        } catch (Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$contextHandler->isAvailable()) {
            throw new NotFoundHttpException();
        }

        $contextRoles = $contextHandler->getRoles($this->tokenStorage->getToken(), $contextSubject);
        $isImpersonated = $contextHandler->isImpersonated($this->tokenStorage->getToken(), $contextSubject);

        if (!$contextSubject || $this->authorization->isGranted('OPEN', $contextSubject)) {
            $openEvent = new OpenContextEvent($context, $contextSubject);
            $this->eventDispatcher->dispatch($openEvent, ContextEvents::OPEN);

            $contextTools = $contextHandler->getTools($contextSubject);

            return new JsonResponse(array_merge($openEvent->getResponse() ?? [], [
                'data' => $contextSubject ? $this->serializer->serialize($contextSubject) : null, // maybe only expose minimal ?

                // 'managed' => $isManager,
                'impersonated' => $isImpersonated,
                'roles' => array_values(array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $contextRoles)),
                // 'accessErrors' => $accessErrors,

                // get all enabled tools for the context, even those inaccessible to the current user
                // this will allow the ui to know if a user try to access a closed tool or a non-existent one.
                'tools' => array_map(function (OrderedTool $orderedTool) use ($context, $contextSubject) {
                    $serializedTool = $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);

                    return array_merge([], $serializedTool, [
                        'status' => $serializedTool['permissions']['open'] ? $this->toolProvider->getStatus($orderedTool->getName(), $context, $contextSubject) : null,
                    ]);
                }, $contextTools),
            ], $contextHandler->getAdditionalData($contextSubject)));
        }

        // return the details of access errors to display it to users
        $accessErrors = $contextHandler->getAccessErrors($this->tokenStorage->getToken(), $contextSubject);

        return new JsonResponse([
            'data' => $contextSubject ? $this->serializer->serialize($contextSubject) : null, // maybe only expose minimal ?
            'impersonated' => $isImpersonated,
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $contextRoles),
            'accessErrors' => $accessErrors,
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
        } catch (Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $contextSubject = $contextHandler->getObject($contextId);
        $contextTools = $contextHandler->getTools($contextSubject);

        if (!$this->authorization->isGranted('ADMINISTRATE', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);

        $this->om->startFlushSuite();

        // update context configuration
        if (!empty($data['data']) && $contextSubject) {
            $this->crud->update($contextSubject, $data['data'], [Crud::NO_PERMISSIONS, Options::PERSIST_TAG]);
        }

        // update tools configuration if any
        if (!empty($data['tools'])) {
            $updatedTools = [];
            foreach ($data['tools'] as $toolData) {
                $updatedTool = new OrderedTool();
                $updatedTool->setContextName($context);
                $updatedTool->setContextId($contextSubject ? $contextSubject->getContextIdentifier() : null);

                $updatedTool = $this->crud->createOrUpdate($updatedTool, $toolData, [Crud::NO_PERMISSIONS]);
                $updatedTools[$updatedTool->getName()] = $updatedTool;
            }

            foreach ($contextTools as $existingTool) {
                if (!array_key_exists($existingTool->getName(), $updatedTools)) {
                    $this->crud->delete($existingTool);
                }
            }

            $contextTools = array_values($updatedTools);
        }

        $this->om->endFlushSuite();

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
        $contextSubject = $contextHandler->getObject($contextId);

        if (!$this->authorization->isGranted('ADMINISTRATE', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $tools = $contextHandler->getAvailableTools($contextSubject);

        return new JsonResponse(array_map(function (ToolInterface $tool) use ($context, $contextSubject) {
            return [
                'icon' => $tool::getIcon(),
                'name' => $tool::getName(),
                'required' => $tool->isRequired($context, $contextSubject),
            ];
        }, $tools));
    }
}
