<?php

namespace Claroline\AppBundle\Controller\Component;

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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the different application contexts (public, desktop, administration, ...).
 *
 * @Route("/context/{context}/{contextId}")
 * @Route("/context/{context}")
 */
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
     *
     * @Route("", name="claro_context_open", methods={"GET"})
     */
    public function openAction(string $context, string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextObject = $contextHandler->getObject($contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $contextRoles = $contextHandler->getRoles($this->tokenStorage->getToken(), $contextObject);
        $isImpersonated = $contextHandler->isImpersonated($this->tokenStorage->getToken(), $contextObject);
        $isManager = $contextHandler->isManager($this->tokenStorage->getToken(), $contextObject);
        $accessErrors = $contextHandler->getAccessErrors($this->tokenStorage->getToken(), $contextObject);

        // $this->authorization->isGranted('OPEN', $contextObject);

        if (empty($accessErrors) || $isManager) {
            $openEvent = new OpenContextEvent($context, $contextObject);
            $this->eventDispatcher->dispatch($openEvent, ContextEvents::OPEN);

            $contextTools = $contextHandler->getTools($contextObject);

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
                'tools' => array_map(function (OrderedTool $orderedTool) use ($context, $contextObject) {
                    $serializedTool = $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);

                    return array_merge([], $serializedTool, [
                        'status' => $serializedTool['permissions']['open'] ? $this->toolProvider->getStatus($orderedTool->getName(), $context, $contextObject) : null,
                    ]);
                }, $contextTools),
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
    public function configureAction(Request $request, string $context, string $contextId = null): JsonResponse
    {
        // retrieve the requested context
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $contextObject = $contextHandler->getObject($contextId);
        $contextTools = $contextHandler->getTools($contextObject);

        $this->authorization->isGranted('ADMINISTRATE', $contextObject);

        $data = $this->decodeRequest($request);

        $this->om->startFlushSuite();
        if (!empty($data['data']) && $contextObject) {
            $this->crud->update($contextObject, $data['data'], [Crud::NO_PERMISSIONS, Options::PERSIST_TAG]);
        }

        if (!empty($data['tools'])) {
            $updatedTools = [];
            foreach ($data['tools'] as $toolData) {
                /** @var OrderedTool $updatedTool */
                $updatedTool = $this->crud->createOrUpdate(OrderedTool::class, $toolData, [Crud::NO_PERMISSIONS]);
                $updatedTool->setContextName($context);
                $updatedTool->setContextId($contextObject ? $contextObject->getContextIdentifier() : null);

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

        return new JsonResponse(array_merge([], [
            'data' => $contextObject ? $this->serializer->serialize($contextObject) : null, // maybe only expose minimal ?
            'tools' => array_map(function (OrderedTool $orderedTool) use ($context, $contextObject) {
                $serializedTool = $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_MINIMAL]);

                return array_merge([], $serializedTool, [
                    'status' => $serializedTool['permissions']['open'] ? $this->toolProvider->getStatus($orderedTool->getName(), $context, $contextObject) : null,
                ]);
            }, $contextTools),
        ], $contextHandler->getAdditionalData($contextObject)));
    }

    /**
     * Gets the list of available tools (all tools implemented, not only the enabled ones in the context).
     *
     * @Route("/tools", name="claro_context_get_available_tools", methods={"GET"})
     */
    public function getAvailableToolsAction(string $context, string $contextId = null): JsonResponse
    {
        $contextHandler = $this->contextProvider->getContext($context);
        $contextSubject = $contextHandler->getObject($contextId);

        $this->authorization->isGranted('ADMINISTRATE', $contextSubject);

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
