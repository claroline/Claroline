<?php

namespace Claroline\ForumBundle\Controller;

use Exception;
use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forumId": "uuid"}})
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
    public function listMessagesAction(Request $request, Subject $subject, Forum $forum = null): JsonResponse
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
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"id": "uuid"}})
     * @ApiDoc(
     *     description="Create a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{id}/message', name: 'create_message', methods: ['POST', 'PUT'])]
    public function createMessage(Subject $subject, Request $request): JsonResponse
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
     * @EXT\ParamConverter("message", class = "Claroline\ForumBundle\Entity\Message",  options={"mapping": {"message": "uuid"}})
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"subject": "uuid"}})
     * @ApiDoc(
     *     description="Udate a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     */
    #[Route(path: '/{subject}/message/{message}', name: 'message_update', methods: ['PUT'])]
    public function updateMessageAction(Subject $subject, Message $message, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $subject, [], true);

        return parent::updateAction($message->getUuid(), $request, Message::class);
    }

    /**
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    #[Route(path: '/forum/{forum}/subjects/list/flagged', name: 'flagged_list', methods: ['GET'])]
    public function getFlaggedSubjectsAction(Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }

    /**
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    #[Route(path: '/forum/{forum}/subjects/list/blocked', name: 'blocked_list', methods: ['GET'])]
    public function getBlockedSubjectsAction(Forum $forum, Request $request): JsonResponse
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
