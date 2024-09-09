<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\ForumManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/forum", name="apiv2_forum_")
 */
class ForumController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ForumManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Forum::class;
    }

    public static function getName(): string
    {
        return 'forum';
    }

    /**
     * @Route("/{id}/subjects", name="list_subjects", methods={"GET"})
     *
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Get the subjects of a forum",
     *     queryString={
     *         "$finder=Claroline\ForumBundle\Entity\Subject&!forum",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The forum id or uuid"}
     *     }
     * )
     */
    public function listSubjectsAction(Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(Subject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$forum->getUuid()], 'moderation' => false]]
            ))
        );
    }

    /**
     * @Route("/{id}/messages", name="list_messages", methods={"GET"})
     *
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
     */
    public function listMessagesAction(Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->crud->list(Message::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$forum->getUuid()], 'moderation' => false]]
            ))
        );
    }

    /**
     * @Route("/{id}/subject", name="create_subject", methods={"POST", "PUT"})
     *
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Create a subject in a forum",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The forum id or uuid"}
     *     }
     * )
     */
    public function createSubjectAction(Forum $forum, Request $request): JsonResponse
    {
        $subject = new Subject();
        $subject->setForum($forum);

        $options = static::getOptions();
        $this->crud->create($subject, $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->serializer->serialize($subject, $options['get'] ?? []),
            201
        );
    }

    /**
     * @Route("/unlock/{user}/forum/{forum}", name="unlock_user", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function unlockAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        // unlock user
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setAccess(true);
        $this->om->persist($validationUser);

        // validate all moderated subjects for this user and forum
        $subjects = $this->om->getRepository(Subject::class)->findBy([
            'forum' => $forum->getUuid(),
            'creator' => $user->getUuid(),
            'moderation' => true,
        ]);

        foreach ($subjects as $subject) {
            $subject->setModerated(Forum::VALIDATE_NONE);
            $this->om->persist($subject);
        }
        // validate all moderated messages for this user and forum
        $messages = $this->om->getRepository(Message::class)->findBy([
            'forum' => $forum->getUuid(),
            'creator' => $user->getUuid(),
            'moderation' => true,
        ]);

        foreach ($messages as $message) {
            $message->setModerated(Forum::VALIDATE_NONE);
            $this->om->persist($message);
        }
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/lock/{user}/forum/{forum}", name="lock_user", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function lockAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setAccess(false);

        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/ban/{user}/forum/{forum}", name="ban", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function banAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setBanned(true);

        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/unban/{user}/forum/{forum}", name="unban", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function unbanAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('EDIT', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setBanned(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/notify/{user}/forum/{forum}", name="notify", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function notifyAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(true);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/unnotify/{user}/forum/{forum}", name="unnotifiy", methods={"PATCH"})
     *
     * @EXT\ParamConverter("user", class = "Claroline\CoreBundle\Entity\User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function unnotifyAction(User $user, Forum $forum): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }
}
