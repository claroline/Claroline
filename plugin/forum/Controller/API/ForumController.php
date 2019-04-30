<?php

namespace Claroline\ForumBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/forum")
 */
class ForumController extends AbstractCrudController
{
    /* @var Manager */
    protected $manager;

    /**
     * ForumController constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.forum_manager")
     * })
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'forum';
    }

    /**
     * @EXT\Route("/{id}/subjects")
     * @EXT\Method("GET")
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
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The forum id or uuid"
     *          }
     *     }
     * )
     *
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSubjectsAction($id, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Subject::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$id], 'moderation' => false]]
            ))
        );
    }

    /**
     * @EXT\Route("/{id}/subject")
     * @EXT\Method({"POST", "PUT"})
     * @EXT\ParamConverter("forum", options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Create a subject in a forum",
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The forum id or uuid"
     *          }
     *     }
     * )
     *
     * @param Forum   $forum
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createSubjectAction(Forum $forum, Request $request)
    {
        $serializedForum = $this->serializer->serialize($forum);
        $data = $this->decodeRequest($request);
        $data['forum'] = $serializedForum;
        $object = $this->crud->create(
            Subject::class,
            $data,
            $this->options['create']
        );

        if (is_array($object)) {
            return new JsonResponse($object, 400);
        }

        return new JsonResponse(
            $this->serializer->serialize($object, $this->options['get']),
            201
        );
    }

    //Pour les 6 méthodes suivantes, utilser le CRUD ? je sais pas trop.

    /**
     * @EXT\Route("/unlock/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function unlockAction(User $user, Forum $forum)
    {
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
     * @EXT\Route("/lock/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function lockAction(User $user, Forum $forum)
    {
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setAccess(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/ban/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function banAction(User $user, Forum $forum)
    {
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setBanned(true);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/unban/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function unbanAction(User $user, Forum $forum)
    {
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setBanned(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/notify/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function notifyAction(User $user, Forum $forum)
    {
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(true);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/unnotify/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param User  $user
     * @param Forum $forum
     *
     * @return JsonResponse
     */
    public function unnotifyAction(User $user, Forum $forum)
    {
        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    public function getClass()
    {
        return Forum::class;
    }
}
