<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Controller\Component;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/tool")
 */
class ToolController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly ContextProvider $contextProvider,
        private readonly ToolProvider $toolProvider
    ) {
    }

    /**
     * Opens a tool.
     *
     * @Route("/open/{name}/{context}/{contextId}", name="claro_tool_open", methods={"GET"})
     */
    public function openAction(string $name, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);

            $orderedTool = $this->toolProvider->getTool($name, $context, $contextHandler->getObject($contextId));
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(array_merge([], $this->toolProvider->open($name, $context, $contextSubject), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * @Route("/configure/{name}/{context}/{contextId}", name="apiv2_tool_configure", methods={"PUT"})
     */
    public function configureAction(Request $request, string $name, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);

            $orderedTool = $this->toolProvider->getTool($name, $context, $contextHandler->getObject($contextId));
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('EDIT', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);

        $this->crud->update($orderedTool, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(array_merge([], $this->toolProvider->configure($name, $context, $contextSubject, $data), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * @Route("/rights/{name}/{context}/{contextId}", name="apiv2_tool_get_rights", methods={"GET"})
     */
    public function getRightsAction(string $name, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);

            $orderedTool = $this->toolProvider->getTool($name, $context, $contextHandler->getObject($contextId));
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('ADMINISTRATE', $orderedTool)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(array_map(function (ToolRights $rights) {
            return $this->serializer->serialize($rights);
        }, $orderedTool->getRights()->toArray()));
    }

    /**
     * @Route("/rights/{name}/{context}/{contextId}", name="apiv2_tool_update_rights", methods={"PUT"})
     */
    public function updateRightsAction(Request $request, string $name, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);

            $orderedTool = $this->toolProvider->getTool($name, $context, $contextHandler->getObject($contextId));
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('ADMINISTRATE', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $requestData = $this->decodeRequest($request);

        $this->om->startFlushSuite();

        $existingRights = $orderedTool->getRights()->toArray();

        $roles = [];
        foreach ($requestData as $right) {
            if (!empty($right['id'])) {
                /** @var ToolRights $toolRights */
                $toolRights = $this->crud->update(ToolRights::class, $right, [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS]);
            } else {
                /** @var ToolRights $toolRights */
                $toolRights = $this->crud->create(ToolRights::class, $right, [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS]);
            }

            $orderedTool->addRight($toolRights);

            // keep reference to the created/update rights, it will be used later to know the ones to delete.
            // I don't use ToolRights id because there is a flush suite, and it is not already generated.
            $roles[] = $toolRights->getRole()->getName();
        }

        // removes rights which no longer exists
        foreach ($existingRights as $existingRight) {
            if (!in_array($existingRight->getRole()->getName(), $roles)) {
                $this->crud->delete($existingRight);
            }
        }

        $this->om->endFlushSuite();

        return $this->getRightsAction($name, $context, $contextId);
    }
}
