<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/forum_message")
 */
class MessageController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function getName(): string
    {
        return 'forum_message';
    }

    public function getClass(): string
    {
        return Message::class;
    }

    public function getIgnore(): array
    {
        return ['list', 'create'];
    }

    /**
     * @Route("/{id}/comment", methods={"POST"})
     * @ParamConverter("message", options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Create a comment in a message",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The message id or uuid"}
     *     }
     * )
     */
    public function createComment(Message $message, Request $request): JsonResponse
    {
        $comment = new Message();
        $comment->setSubject($message->getSubject());
        $comment->setParent($message);

        $this->crud->create($comment, $this->decodeRequest($request), array_merge($this->options['create'], [Crud::THROW_EXCEPTION]));

        return new JsonResponse(
            $this->serializer->serialize($comment, $this->options['get']),
            201
        );
    }

    /**
     * @Route("forum/{forum}/messages/list/flagged", name="apiv2_forum_message_flagged_list", methods={"GET"})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function getFlaggedMessagesAction(Request $request, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->finder->search($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
      );
    }

    /**
     * @Route("forum/{forum}/messages/list/blocked", name="apiv2_forum_message_blocked_list", methods={"GET"})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function getBlockedMessagesAction(Request $request, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->finder->search($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['moderation' => true, 'forum' => $forum->getUuid()]]
            ))
      );
    }
}
