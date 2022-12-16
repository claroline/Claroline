<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
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
 * @Route("/forum")
 */
class ForumController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /* @var ForumManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ForumManager $manager
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    public function getClass(): string
    {
        return Forum::class;
    }

    public function getName(): string
    {
        return 'forum';
    }

    /**
     * @Route("/{id}/subjects", name="apiv2_forum_list_subjects", methods={"GET"})
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
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
            $this->finder->search(Subject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$forum->getUuid()], 'moderation' => false]]
            ))
        );
    }

    /**
     * @Route("/{id}/messages", name="apiv2_forum_list_messages", methods={"GET"})
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
     */
    public function listMessagesAction(Forum $forum, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        return new JsonResponse(
            $this->finder->search(Message::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$forum->getUuid()], 'moderation' => false]]
            ))
        );
    }

    /**
     * @Route("/{id}/subject", methods={"POST", "PUT"})
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

        $this->crud->create($subject, $this->decodeRequest($request), array_merge($this->options['create'], [Crud::THROW_EXCEPTION]));

        return new JsonResponse(
            $this->serializer->serialize($subject, $this->options['get']),
            201
        );
    }

    /**
     * @Route("/unlock/{user}/forum/{forum}", methods={"PATCH"})
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

        $filters = [
            'forum' => $forum->getUuid(),
            'creatorId' => $user->getUuid(),
            'moderation' => true,
        ];
        // validate all moderated subjects for this user and forum
        $subjects = $this->finder->get(Subject::class)->find($filters);

        foreach ($subjects as $subject) {
            $subject->setModerated(Forum::VALIDATE_NONE);
            $this->om->persist($subject);
        }
        // validate all moderated messages for this user and forum
        $messages = $this->finder->get(Message::class)->find($filters);

        foreach ($messages as $message) {
            $message->setModerated(Forum::VALIDATE_NONE);
            $this->om->persist($message);
        }
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @Route("/lock/{user}/forum/{forum}", methods={"PATCH"})
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
     * @Route("/ban/{user}/forum/{forum}", methods={"PATCH"})
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
     * @Route("/unban/{user}/forum/{forum}", methods={"PATCH"})
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
     * @Route("/notify/{user}/forum/{forum}", methods={"PATCH"})
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
     * @Route("/unnotify/{user}/forum/{forum}", methods={"PATCH"})
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
