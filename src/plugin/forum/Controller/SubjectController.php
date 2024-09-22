<?php

namespace Claroline\ForumBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Exception;
use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/forum_subject', name: 'apiv2_forum_subject_')]
class SubjectController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'forum_subject';
    }

    public static function getClass(): string
    {
        return Subject::class;
    }

    public function getIgnore(): array
    {
        return ['list', 'create'];
    }

    /**
     *
     *
     * @ApiDoc(
     *     description="Get the messages of a subject",
     *     queryString={
     *         "$finder=Claroline\ForumBundle\Entity\Message&!parent&!subject",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{id}/messages', methods: ['GET'])]
    #[Route(path: '/forum/{forumId}/subjects/{id}/messages', name: 'get_message', methods: ['GET'])]
    public function listMessagesAction(Request $request, #[MapEntity(class: 'Claroline\ForumBundle\Entity\Subject', mapping: ['id' => 'uuid'])]
    Subject $subject, #[MapEntity(class: 'Claroline\ForumBundle\Entity\Forum', mapping: ['forumId' => 'uuid'])]
    Forum $forum = null): JsonResponse
    {
        if ($forum && ($forum->getId() !== $subject->getForum()->getId())) {
            throw new Exception('This subject was not created in the forum.');
        }

        $this->checkPermission('OPEN', $subject, [], true);

        return new JsonResponse(
            $this->crud->list(Message::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['subject' => $subject->getUuid(), 'parent' => null, 'first' => false]]
            ))
        );
    }

    /**
     *
     * @ApiDoc(
     *     description="Create a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{id}/message', name: 'create_message', methods: ['POST', 'PUT'])]
    public function createMessage(#[MapEntity(class: 'Claroline\ForumBundle\Entity\Subject', mapping: ['id' => 'uuid'])]
    Subject $subject, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $subject, [], true);

        $options = static::getOptions();

        $message = new Message();
        $message->setSubject($subject);

        $this->crud->create($message, $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->serializer->serialize($message, $options['get'] ?? []),
            201
        );
    }

    /**
     *
     * @ApiDoc(
     *     description="Udate a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{subject}/message/{message}', name: 'message_update', methods: ['PUT'])]
    public function updateMessageAction(#[MapEntity(class: 'Claroline\ForumBundle\Entity\Subject', mapping: ['subject' => 'uuid'])]
    Subject $subject, #[MapEntity(class: 'Claroline\ForumBundle\Entity\Message', mapping: ['message' => 'uuid'])]
    Message $message, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $subject, [], true);

        return parent::updateAction($message->getUuid(), $request, Message::class);
    }

    #[Route(path: '/forum/{forum}/subjects/list/flagged', name: 'flagged_list', methods: ['GET'])]
    public function getFlaggedSubjectsAction(#[MapEntity(class: 'Claroline\ForumBundle\Entity\Forum', mapping: ['forum' => 'uuid'])]
    Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }

    #[Route(path: '/forum/{forum}/subjects/list/blocked', name: 'blocked_list', methods: ['GET'])]
    public function getBlockedSubjectsAction(#[MapEntity(class: 'Claroline\ForumBundle\Entity\Forum', mapping: ['forum' => 'uuid'])]
    Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['moderation' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }
}
