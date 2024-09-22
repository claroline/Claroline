<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/message', name: 'apiv2_message_')]
class MessageController extends AbstractCrudController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageManager $messageManager
    ) {
    }

    public static function getName(): string
    {
        return 'message';
    }

    public static function getClass(): string
    {
        return Message::class;
    }

    /**
     * @ApiDoc(
     *     description="Returns the list of received message for the current connected user",
     *     queryString={
     *         "$finder=Claroline\MessageBundle\Entity\Message&!removed&!sent",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/received', name: 'received', methods: ['GET'])]
    public function getReceivedAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['removed' => false, 'sent' => false]]
            ))
        );
    }

    /**
     * @ApiDoc(
     *     description="Returns the list of removed message for the current connected user",
     *     queryString={
     *         "$finder=Claroline\MessageBundle\Entity\Message&!removed",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/removed', name: 'removed', methods: ['GET'])]
    public function getRemovedAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['removed' => true]]
            ))
        );
    }

    /**
     * @ApiDoc(
     *     description="Returns the list of sent message for the current connected user",
     *     queryString={
     *         "$finder=Claroline\MessageBundle\Entity\Message&!removed&!sent&!removed",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/sent', name: 'sent', methods: ['GET'])]
    public function getSentAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['sent' => true, 'removed' => false]]
            ))
        );
    }

    /**
     * @ApiDoc(
     *     description="Soft delete an array of messages.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
    #[Route(path: '/softdelete', name: 'soft_delete', methods: ['PUT'])]
    public function softDeleteAction(Request $request): JsonResponse
    {
        $messages = $this->decodeIdsString($request, UserMessage::class);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->crud->replace($message, 'removed', true);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    /**
     * @ApiDoc(
     *     description="Hard delete an array of messages.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
    #[Route(path: '/remove', name: 'hard_delete', methods: ['DELETE'])]
    public function hardDeleteAction(Request $request): JsonResponse
    {
        $messages = $this->decodeIdsString($request, UserMessage::class);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->messageManager->remove($message);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Restore a list of messages for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
    #[Route(path: '/restore', name: 'restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $messages = $this->decodeIdsString($request, UserMessage::class);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->crud->replace($message, 'removed', false);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    /**
     * @ApiDoc(
     *     description="Read an array of message for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
    #[Route(path: '/read', name: 'read', methods: ['PUT'])]
    public function readAction(Request $request): JsonResponse
    {
        $messages = $this->decodeIdsString($request, UserMessage::class);

        $this->om->startFlushSuite();

        foreach ($messages as $message) {
            $this->crud->replace($message, 'isRead', true);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    /**
     * @ApiDoc(
     *     description="Unread an array of message for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
    #[Route(path: '/unread', name: 'unread', methods: ['PUT'])]
    public function unreadAction(Request $request): JsonResponse
    {
        $messages = $this->decodeIdsString($request, UserMessage::class);

        $this->om->startFlushSuite();

        foreach ($messages as $message) {
            $this->crud->replace($message, 'isRead', false);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    /**
     *
     * @ApiDoc(
     *     description="Get the fist message.",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The message id or uuid"}
     *     }
     * )
     * @param int|string $id
     */
    #[Route(path: '/root/{id}', name: 'root', methods: ['GET'])]
    public function getRootAction($id): JsonResponse
    {
        $message = $this->crud->get($this->getClass(), $id);
        $rootId = $message->getRoot();
        $root = $this->om->getRepository($this->getClass())->find($rootId);

        return new JsonResponse($this->serializer->serialize($root, [Options::IS_RECURSIVE]));
    }

    public function getAction(Request $request, string $field, string $id): JsonResponse
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $object = $this->crud->get($this->getClass(), $id);
        $um = $this->om->getRepository(UserMessage::class)->findOneBy(['message' => $object, 'user' => $currentUser]);
        $this->crud->replace($um, 'isRead', true);

        $options = static::getOptions();

        if ($object) {
            return new JsonResponse(
                $this->serializer->serialize($object, $options['get'] ?? [])
            );
        }

        return new JsonResponse("No object found for id {$id} of class {$this->getClass()}", 404);
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'get' => [Options::IS_RECURSIVE],
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return [
            'user' => $user->getUuid(),
        ];
    }
}
