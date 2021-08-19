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
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Claroline\MessageBundle\Manager\MessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/message")
 */
class MessageController extends AbstractCrudController
{
    private $tokenStorage;
    /** @var MessageManager */
    private $messageManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MessageManager $messageManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->messageManager = $messageManager;
    }

    /** @return string */
    public function getName()
    {
        return 'message';
    }

    /**
     * @Route("/count/unread", name="apiv2_message_count_unread", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function countUnreadAction(): JsonResponse
    {
        return new JsonResponse(
            $this->finder->fetch(Message::class, [
                'read' => false,
                'removed' => false,
                'sent' => false,
            ], null, 0, -1, true)
        );
    }

    /**
     * @Route("/received", name="apiv2_message_received", methods={"GET"})
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
    public function getReceivedAction(Request $request): JsonResponse
    {
        return new JsonResponse(
          $this->finder->search($this->getClass(), array_merge(
              $request->query->all(),
              ['hiddenFilters' => ['removed' => false, 'sent' => false]]
          ))
        );
    }

    /**
     * @Route("/removed", name="apiv2_message_removed", methods={"GET"})
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
    public function getRemovedAction(Request $request): JsonResponse
    {
        return new JsonResponse(
          $this->finder->search($this->getClass(), array_merge(
              $request->query->all(),
              ['hiddenFilters' => ['removed' => true]]
          ))
        );
    }

    /**
     * @Route("/sent", name="apiv2_message_sent", methods={"GET"})
     *
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
    public function getSentAction(Request $request): JsonResponse
    {
        return new JsonResponse(
          $this->finder->search($this->getClass(), array_merge(
              $request->query->all(),
              ['hiddenFilters' => ['sent' => true, 'removed' => false]]
          ))
        );
    }

    /**
     * @Route("/softdelete", name="apiv2_message_soft_delete", methods={"PUT"})
     * @ApiDoc(
     *     description="Soft delete an array of messages.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
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
     * @Route("/remove", name="apiv2_message_hard_delete", methods={"DELETE"})
     * @ApiDoc(
     *     description="Hard delete an array of messages.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
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
     * @Route("/restore", name="apiv2_message_restore", methods={"PUT"})
     * @ApiDoc(
     *     description="Restore a list of messages for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
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
     * @Route("/read", name="apiv2_message_read", methods={"PUT"})
     * @ApiDoc(
     *     description="Read an array of message for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
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
     * @Route("/unread", name="apiv2_message_unread", methods={"PUT"})
     * @ApiDoc(
     *     description="Unread an array of message for the current user.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "The message ids list."}
     *     }
     * )
     */
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
     * @Route("/root/{id}", name="apiv2_message_root", methods={"GET"})
     * @ApiDoc(
     *     description="Get the fist message.",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The message id or uuid"}
     *     }
     * )
     *
     * @param int|string $id
     */
    public function getRootAction($id): JsonResponse
    {
        $message = $this->crud->get($this->getClass(), $id);
        $rootId = $message->getRoot();
        $root = $this->om->getRepository($this->getClass())->find($rootId);

        return new JsonResponse($this->serializer->serialize($root, [Options::IS_RECURSIVE]));
    }

    public function getAction(Request $request, $id, $class)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $query = $request->query->all();
        $object = $this->crud->get($class, $id);
        $um = $this->om->getRepository(UserMessage::class)->findOneBy(['message' => $object, 'user' => $currentUser]);
        $this->crud->replace($um, 'isRead', true);
        $options = $this->options['get'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        return $object ?
            new JsonResponse(
                $this->serializer->serialize($object, $options)
            ) :
            new JsonResponse("No object found for id {$id} of class {$class}", 404);
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'get' => [Options::IS_RECURSIVE],
        ]);
    }

    public function getClass()
    {
        return Message::class;
    }
}
