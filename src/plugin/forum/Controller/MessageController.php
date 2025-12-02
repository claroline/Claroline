<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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

    #[Route(path: '/{id}/comment', name: 'create_comment', methods: ['POST'])]
    public function createComment(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Message $message,
        Request $request
    ): JsonResponse {
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
    public function listFlaggedAction(
        #[MapEntity(mapping: ['forum' => 'uuid'])]
        Forum $forum,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        $finderRequest->addFilter('forum', $forum->getUuid());
        $finderRequest->addFilter('flagged', true);

        $subjects = $this->crud->search(Message::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $subjects->toResponse();
    }
}
