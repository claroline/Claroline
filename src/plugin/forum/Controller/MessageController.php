<?php

namespace Claroline\ForumBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/forum_message', name: 'apiv2_forum_message_')]
class MessageController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'forum_message';
    }

    public static function getClass(): string
    {
        return Message::class;
    }

    public function getIgnore(): array
    {
        return ['list', 'create'];
    }

    /**
     *
     * @ApiDoc(
     *     description="Create a comment in a message",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The message id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{id}/comment', name: 'create_comment', methods: ['POST'])]
    public function createComment(#[MapEntity(mapping: ['id' => 'uuid'])]
    Message $message, Request $request): JsonResponse
    {
        $options = static::getOptions();

        $comment = new Message();
        $comment->setSubject($message->getSubject());
        $comment->setParent($message);

        $this->crud->create($comment, $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->serializer->serialize($comment, $options['get'] ?? []),
            201
        );
    }

    #[Route(path: '/forum/{forum}/messages/list/flagged', name: 'flagged_list', methods: ['GET'])]
    public function getFlaggedMessagesAction(Request $request, #[MapEntity(class: 'Claroline\ForumBundle\Entity\Forum', mapping: ['forum' => 'uuid'])]
    Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }

    #[Route(path: '/forum/{forum}/messages/list/blocked', name: 'blocked_list', methods: ['GET'])]
    public function getBlockedMessagesAction(Request $request, #[MapEntity(class: 'Claroline\ForumBundle\Entity\Forum', mapping: ['forum' => 'uuid'])]
    Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['moderation' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }
}
