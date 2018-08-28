<?php

namespace Claroline\ForumBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/forum")
 */
class ForumController extends AbstractCrudController
{
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
            $this->finder->search('Claroline\ForumBundle\Entity\Subject', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['forum' => [$id], 'moderation' => Forum::VALIDATE_NONE]]
            ))
        );
    }

    /**
     * @EXT\Route("/{id}/subject")
     * @EXT\Method({"POST", "PUT"})
     * @ParamConverter("forum", options={"mapping": {"id": "uuid"}})
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
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createSubjectAction(Forum $forum, Request $request)
    {
        $forum = $this->serializer->serialize($forum);
        $data = $this->decodeRequest($request);
        $data['forum'] = $forum;
        $object = $this->crud->create(
            'Claroline\ForumBundle\Entity\Subject',
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
     */
    public function unlockAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setAccess(true);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/lock/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function lockAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setAccess(false);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/ban/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function banAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setBanned(true);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/unban/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function unbanAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setBanned(false);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/notify/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function notifyAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setNotified(true);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    /**
     * @EXT\Route("/unnotify/{user}/forum/{forum}")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("forum", class = "ClarolineForumBundle:Forum",  options={"mapping": {"forum": "uuid"}})
     */
    public function unnotifyAction(User $user, Forum $forum)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $user = $this->container->get('claroline.manager.forum_manager')->getValidationUser($user, $forum);
        $user->setNotified(false);
        $om->persist($user);
        $om->flush();

        return new JsonResponse(true);
    }

    public function getClass()
    {
        return "Claroline\ForumBundle\Entity\Forum";
    }
}
