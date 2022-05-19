<?php

namespace Claroline\ForumBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forum_subject")
 */
class SubjectController extends AbstractCrudController
{
    public function getName()
    {
        return 'forum_subject';
    }

    /**
     * @Route("/{id}/messages", methods={"GET"})
     * @Route("/forum/{forumId}/subjects/{id}/messages", name="apiv2_forum_subject_get_message", methods={"GET"})
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forumId": "uuid"}})

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
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function getMessagesAction(Subject $subject, Forum $forum = null, Request $request)
    {
        if ($forum && ($forum->getId() !== $subject->getForum()->getId())) {
            throw new \Exception('This subject was not created in the forum.');
        }

        return new JsonResponse(
          $this->finder->search(Message::class, array_merge(
              $request->query->all(),
              ['hiddenFilters' => ['subject' => $subject->getId(), 'parent' => null, 'first' => false]]
            ))
        );
    }

    /**
     * @Route("/{id}/message", methods={"POST", "PUT"})
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Create a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     *
     * @return JsonResponse
     */
    public function createMessage(Subject $subject, Request $request)
    {
        $subject = $this->serializer->serialize($subject);
        $data = $this->decodeRequest($request);
        $data['subject'] = $subject;

        $object = $this->crud->create(
            Message::class,
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

    /**
     * @Route("/{subject}/message/{message}", name="apiv2_forum_subject_message_update", methods={"PUT"})
     * @EXT\ParamConverter("message", class = "Claroline\ForumBundle\Entity\Message",  options={"mapping": {"message": "uuid"}})
     * @EXT\ParamConverter("subject", class = "Claroline\ForumBundle\Entity\Subject",  options={"mapping": {"subject": "uuid"}})
     *
     * @ApiDoc(
     *     description="Udate a message in a subject",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"},  "description": "The subject id or uuid"}
     *     }
     * )
     *
     * @return JsonResponse
     */
    public function updateMessageAction(Subject $subject, Message $message, Request $request)
    {
        return parent::updateAction($message->getUuid(), $request, Message::class);
    }

    /**
     * @Route("/forum/{forum}/subjects/list/flagged", name="apiv2_forum_subject_flagged_list", methods={"GET"})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getFlaggedSubjectsAction(Forum $forum, Request $request)
    {
        return new JsonResponse(
        $this->finder->search($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
      );
    }

    /**
     * @Route("/forum/{forum}/subjects/list/blocked", name="apiv2_forum_subject_blocked_list", methods={"GET"})
     * @EXT\ParamConverter("forum", class = "Claroline\ForumBundle\Entity\Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getBlockedSubjectsAction(Forum $forum, Request $request)
    {
        return new JsonResponse(
            $this->finder->search($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['moderation' => true, 'forum' => $forum->getUuid()]]
            ))
        );
    }

    public function getClass()
    {
        return Subject::class;
    }
}
