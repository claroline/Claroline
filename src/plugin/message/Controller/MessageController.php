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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/message', name: 'apiv2_message_')]
class MessageController extends AbstractCrudController
{
    public function __construct(
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

    #[Route(path: '/softdelete', name: 'soft_delete', methods: ['PUT'])]
    public function softDeleteAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $messages = $this->om->getRepository(UserMessage::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->crud->replace($message, 'removed', true);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    #[Route(path: '/remove', name: 'hard_delete', methods: ['DELETE'])]
    public function hardDeleteAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $messages = $this->om->getRepository(UserMessage::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->messageManager->remove($message);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/restore', name: 'restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $messages = $this->om->getRepository(UserMessage::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($messages as $message) {
            $this->crud->replace($message, 'removed', false);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    #[Route(path: '/read', name: 'read', methods: ['PUT'])]
    public function readAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $messages = $this->om->getRepository(UserMessage::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();

        foreach ($messages as $message) {
            $this->crud->replace($message, 'read', true);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

    #[Route(path: '/unread', name: 'unread', methods: ['PUT'])]
    public function unreadAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $messages = $this->om->getRepository(UserMessage::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();

        foreach ($messages as $message) {
            $this->crud->replace($message, 'read', false);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (UserMessage $message) {
            return $this->serializer->serialize($message->getMessage());
        }, $messages));
    }

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
        $currentUser = $this->tokenStorage->getToken()?->getUser();

        $object = $this->crud->get($this->getClass(), $id);
        $um = $this->om->getRepository(UserMessage::class)->findOneBy(['message' => $object, 'user' => $currentUser]);
        $this->crud->replace($um, 'read', true);

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
}
