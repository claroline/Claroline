<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\EditTitleType;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Claroline\ForumBundle\Event\Log\EditMessageEvent;
use Claroline\ForumBundle\Event\Log\EditSubjectEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\User;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * ForumController
 */
class ForumController extends Controller
{
    /**
     * @Route(
     *     "/{forumId}/subjects/page/{page}",
     *     name="claro_forum_subjects",
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("ClarolineForumBundle::index.html.twig")
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function openAction($forumId, $page, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('ClarolineForumBundle:Forum')->find($forumId);
        $this->checkAccess($forum);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getSubjects();
        $subjects = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($forum);
        $adapter = new ArrayAdapter($subjects);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $collection = new ResourceCollection(array($forum->getResourceNode()));
        $canCreateSubject = $this->get('security.context')->isGranted('post', $collection);
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', $collection);

        return array(
            'pager' => $pager,
            '_resource' => $forum,
            'canCreateSubject' => $canCreateSubject,
            'isModerator' => $isModerator,
            'hasSubscribed' => $this->get('claroline.manager.forum_manager')->hasSubscribed($user, $forum)
        );
    }

    /**
     * @Route(
     *     "/form/subject/{forumId}",
     *     name="claro_forum_form_subject_creation"
     * )
     *
     * @Template("ClarolineForumBundle::subjectForm.html.twig")
     *
     * @param integer $forumId
     *
     * @return Response
     */
    public function forumSubjectCreationFormAction($forumId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $forum = $em->getRepository('ClarolineForumBundle:Forum')->find($forumId);
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $formSubject = $this->get('form.factory')->create(new SubjectType());

        return array(
            '_resource' => $forum,
            'form' => $formSubject->createView()
        );
    }

    /*
     * The form submission is working but I had to do some weird things to make it works.
     */
    /**
     * @Route(
     *     "/subject/create/{forumId}",
     *     name="claro_forum_create_subject"
     * )
     *
     * @Template("ClarolineForumBundle::subjectForm.html.twig")
     *
     * @param integer $forumId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createSubjectAction($forumId)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('ClarolineForumBundle:Forum')->find($forumId);
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $subject = $form->getData();
            $subject->setCreator($user);
            //instantiation of the new resources
            $subject->setForum($forum);
            $this->get('claroline.manager.forum_manager')->createSubject($subject);
            $dataMessage = $form->get('message')->getData();

            if ($dataMessage['content'] !== null) {
                $message = new Message();
                $message->setContent($dataMessage['content']);
                $message->setCreator($user);
                $message->setSubject($subject);
                $this->get('claroline.manager.forum_manager')->createMessage($message);

                return new RedirectResponse(
                    $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId(), '_resource' => $forum))
                );
            }
        }

        throw new \Exception($form->getErrorsAsString());
        $form->get('message')->addError(
            new FormError($this->get('translator')->trans('field_content_required', array(), 'forum'))
        );

        return array(
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/subject/{subjectId}/messages/page/{page}",
     *     name="claro_forum_messages",
     *     defaults={"page"=1}
     * )
     *
     * @Template("ClarolineForumBundle::messages.html.twig")
     *
     * @return Response
     */
    public function showMessagesAction($subjectId, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $forum = $subject->getForum();
        $this->checkAccess($forum);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getMessages();
        $query = $em->getRepository('ClarolineForumBundle:Message')->findBySubject($subject, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $isModerator = $this->get('security.context')->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));
        $collection = new ResourceCollection(array($forum->getResourceNode()));
        $canAnswer = $this->get('security.context')->isGranted('post', $collection);
        $form = $this->get('form.factory')->create(new MessageType());

        return array(
            'subject' => $subject,
            'pager' => $pager,
            '_resource' => $forum,
            'isModerator' => $isModerator,
            'form' => $form->createView(),
            'canAnswer' => $canAnswer
        );
    }

    /**
     * @Route(
     *     "/add/message/{subjectId}",
     *     name="claro_forum_message_form"
     * )
     *
     * @Template("ClarolineForumBundle::messageForm.html.twig")
     *
     * @param integer $subjectId
     *
     * @return Response
     */
    public function messageCreationFormAction($subjectId)
    {
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $forum = $subject->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new MessageType());

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/create/message/{subjectId}",
     *     name="claro_forum_create_message"
     * )
     *
     * @Template("ClarolineForumBundle::messageForm.html.twig")
     *
     * @param integer $subjectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createMessageAction($subjectId)
    {
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));
        $manager = $this->get('claroline.manager.forum_manager');
        $subject = $manager->getSubject($subjectId);
        $forum = $subject->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        if ($form->isValid()) {
            $message = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $message->setCreator($user);
            $message->setSubject($subject);
            $manager->createMessage($message);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subjectId' => $subjectId))
            );
        }

        return array(
            'subjectId' => $subjectId,
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/options/edit",
     *     name="claro_forum_edit_options"
     * )
     *
     * @Template("ClarolineForumBundle::pluginOptionsForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editForumOptionsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $forumOptions = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $form = $this->container->get('form.factory')->create(new ForumOptionsType(), $forumOptions[0]);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $forumOptions = $form->getData();
            $em->persist($forumOptions);
            $em->flush();

            return new RedirectResponse($this->generateUrl('claro_admin_plugins'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/edit/message/{message}/form",
     *     name="claro_forum_edit_message_form"
     * )
     *
     * @Template("ClarolineForumBundle::editMessageForm.html.twig")
     */
    public function editMessageFormAction(Message $message)
    {
        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new MessageType(), $message);

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/edit/message/{message}",
     *     name="claro_forum_edit_message"
     * )
     *
     * @Template("ClarolineForumBundle::editMessageForm.html.twig")
     */
    public function editMessageAction(Message $message)
    {
        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));
        $em = $this->getDoctrine()->getManager();

        if ($form->isValid()) {
            $this->dispatch(new EditMessageEvent($message, $message->getContent(), $form->get('content')->getData()));
            $message->setContent($form->get('content')->getData());
            $em->persist($message);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subjectId' => $subject->getId()))
            );
        }

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/{forum}/search/{search}/page/{page}",
     *     name="claro_forum_search",
     *     defaults={"page"=1, "search"= ""},
     *     options={"expose"=true}
     * )
     * @Template("ClarolineForumBundle::searchResults.html.twig")
     */
    public function searchAction(Forum $forum, $page, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ClarolineForumBundle:Forum');
        $query = $repo->search($forum, $search);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, '_resource' => $forum, 'search' => $search, 'page' => $page);
    }

     /**
     * @Route(
     *     "/edit/subject/{subjectId}/form",
     *     name="claro_forum_edit_subject_form"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template("ClarolineForumBundle::editSubjectForm.html.twig")
     *
     */
    public function editSubjectFormAction(Subject $subject)
    {
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($subject->getForum()->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);

        return array(
            'form' => $form->createView(),
            'subjectId' => $subject->getId(),
            'forumId' => $subject->getForum()->getId()
        );
    }

    /**
     * @Route(
     *     "/edit/subject/{subjectId}/submit",
     *     name="claro_forum_edit_subject"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template("ClarolineForumBundle::editSubjectForm.html.twig")
     */
    public function editSubjectAction(Subject $subject)
    {
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($subject->getForum()->getResourceNode())));
        $em = $this->getDoctrine()->getManager();

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $oldTitle = $subject->getTitle();
        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em->persist($subject);
            $em->flush();
            $this->dispatch(new EditSubjectEvent($subject, $oldTitle, $subject->getTitle()));

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('forumId' => $subject->getForum()->getId()))
            );
        }

       return array(
            'form' => $form->createView(),
            'subjectId' => $subject->getId(),
            'forumId' => $subject->getForum()->getId()
        );
    }

    /**
     * @Route(
     *     "/delete/message/{message}",
     *     name="claro_forum_delete_message"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     */
    public function deleteMessageAction(Message $message)
    {
        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($message->getSubject()->getForum()->getResourceNode())))) {
            $this->get('claroline.manager.forum_manager')->deleteMessage($message);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subjectId' => $message->getSubject()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @Route(
     *     "/subscribe/forum/{forum}",
     *     name="claro_forum_subscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     */
    public function subscribeAction(Forum $forum, User $user)
    {
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->subscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/unsubscribe/forum/{forum}",
     *     name="claro_forum_unsubscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     */
    public function unsubscribeAction(Forum $forum, User $user)
    {
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->unsubscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/delete/subject/{subject}",
     *     name="claro_forum_delete_subject"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function deleteSubjectAction(Subject $subject)
    {
        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($subject->getForum()->getResourceNode())))) {

            $this->get('claroline.manager.forum_manager')->deleteSubject($subject);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('forumId' => $subject->getForum()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    private function checkAccess(Forum $forum)
    {
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }
    }

    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }
}
