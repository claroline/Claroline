<?php

namespace Claroline\ForumBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/forum_subject")
 */
class SubjectController extends AbstractCrudController
{
    public function getName()
    {
        return 'forum_subject';
    }

    /**
     * @EXT\Route("/{id}/messages")
     * @EXT\Method("GET")

     * @ApiDoc(
     *     description="Get the messages of a subject",
     *     queryString={
     *         "$finder=Claroline\ForumBundle\Entity\Message&!parent&!subject",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The subject id or uuid"
     *          }
     *     }
     * )
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMessagesAction($id, Request $request)
    {
        return new JsonResponse(
          $this->finder->search('Claroline\ForumBundle\Entity\Message', array_merge(
              $request->query->all(),
              ['hiddenFilters' => ['subject' => $id, 'parent' => null]]
            ))
        );
    }

    /**
     * @EXT\Route("/{id}/message")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("subject", class = "ClarolineForumBundle:Subject",  options={"mapping": {"id": "uuid"}})
     *
     * @ApiDoc(
     *     description="Create a message in a subject",
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The subject id or uuid"
     *          }
     *     }
     * )
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createMessage(Subject $subject, Request $request)
    {
        $subject = $this->serializer->serialize($subject);
        $data = $this->decodeRequest($request);
        $data['subject'] = $subject;

        $object = $this->crud->create(
            'Claroline\ForumBundle\Entity\Message',
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
     * @EXT\Route("/{subject}/message/{message}", name="apiv2_forum_subject_message_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("message", class = "ClarolineForumBundle:Message",  options={"mapping": {"message": "uuid"}})
     * @EXT\ParamConverter("subject", class = "ClarolineForumBundle:Subject",  options={"mapping": {"subject": "uuid"}})
     *
     * @ApiDoc(
     *     description="Udate a message in a subject",
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The subject id or uuid"
     *          }
     *     }
     * )
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateMessageAction(Subject $subject, Message $message, Request $request)
    {
        return parent::updateAction($message->getUuid(), $request, 'Claroline\ForumBundle\Entity\Message');
    }

    /**
     * @EXT\Route("/forum/{forum}/tag/{tag}", name="apiv2_search_subjects_tag")
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function findByTagAction(Forum $forum, $tag)
    {
        $options = [
            'tag' => $tag,
            'strict' => false,
            'class' => 'Claroline\ForumBundle\Entity\Subject',
            'object_response' => true,
        ];

        $event = $this->container->get('event_dispatcher')
            ->dispatch(
              'claroline_retrieve_tagged_objects',
              new GenericDataEvent($options)
            );

        $subjects = $event->getResponse();

        return new JsonResponse(
            array_map(function (Subject $subject) {
                return $this->serializer->serialize($subject, $this->options['get']);
            }, $subjects),
            200
        );
    }

    /**
     * @EXT\Route("forum/{forum}/subjects/list/flagged", name="apiv2_forum_subject_flagged_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFlaggedSubjectsAction(Request $request, Forum $forum)
    {
        return new JsonResponse(
        $this->finder->search($this->getClass(), array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['flagged' => true, 'forum' => $forum->getUuid()]]
            ))
      );
    }

    public function getClass()
    {
        return "Claroline\ForumBundle\Entity\Subject";
    }
}
