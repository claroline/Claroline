<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


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
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function openAction($forumId, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($forumId);
        $this->checkAccess($forum);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getSubjects();
        $query = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($forum, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $this->render(
            'ClarolineForumBundle::index.html.twig', array(
                'forum' => $forum,
                'pager' => $pager,
                'workspace' => $forum->getWorkspace()
            )
        );
    }

    /**
     * @Route(
     *     "/form/subject/{forumId}",
     *     name="claro_forum_form_subject_creation"
     * )
     *
     * @param integer $forumId
     *
     * @return Response
     */
    public function forumSubjectCreationFormAction($forumId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($forumId);
        $this->checkAccess($forum);
        $formSubject = $this->get('form.factory')->create(new SubjectType());
        $workspace = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($forumId)
            ->getWorkspace();

        return $this->render(
            'ClarolineForumBundle::subject_form.html.twig',
            array(
                'form' => $formSubject->createView(),
                'forumId' => $forumId,
                'workspace' => $workspace
            )
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
     * @param integer $forumId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createSubjectAction($forumId)
    {
        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getManager();
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($forumId);
        $this->checkAccess($forum);

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $subject = $form->getData();
            $subject->setCreator($user);
            //instantiation of the new resources
            $subject->setForum($forum);
            $em->persist($subject);
            $dataMessage = $subject->getMessage();

            if ($dataMessage['content'] !== null) {
                $message = new Message();
                $message->setContent($dataMessage['content']);
                $message->setCreator($user);
                $message->setSubject($subject);
                $em->persist($message);
                $em->flush();

                return new RedirectResponse(
                    $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId()))
                );
            }
        }

        $form->get('message')->addError(
            new FormError($this->get('translator')->trans('field_content_required', array(), 'forum'))
        );

        return $this->render(
            'ClarolineForumBundle::subject_form.html.twig',
            array(
                'form' => $form->createView(),
                'forumId' => $forum->getId(),
                'workspace' => $forum->getWorkspace()
            )
        );
    }

    /**
     * @Route(
     *     "/subject/{subjectId}/messages/page/{page}",
     *     name="claro_forum_messages",
     *     defaults={"page"=1}
     * )
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

        return $this->render(
            'ClarolineForumBundle::messages.html.twig',
            array(
                'subject' => $subject,
                'workspace' => $subject->getForum()->getWorkspace(),
                'pager' => $pager
            )
        );
    }

    /**
     * @Route(
     *     "/add/message/{subjectId}",
     *     name="claro_forum_message_form"
     * )
     *
     * @param integer $subjectId
     *
     * @return Response
     */
    public function messageCreationFormAction($subjectId)
    {
        $form = $this->get('form.factory')->create(new MessageType());
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $forum = $subject->getForum();
        $this->checkAccess($forum);

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig',
            array(
                'subjectId' => $subjectId,
                'form' => $form->createView(),
                'workspace' => $subject->getForum()->getWorkspace()
            )
        );
    }

    /**
     * @Route(
     *     "/create/message/{subjectId}",
     *     name="claro_forum_create_message"
     * )
     *
     * @param integer $subjectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createMessageAction($subjectId)
    {
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $forum = $subject->getForum();
        $this->checkAccess($forum);

        if ($form->isValid()) {
            $message = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $message->setCreator($user);
            $message->setSubject($subject);
            $em->persist($message);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subjectId' => $subjectId))
            );
        }

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig',
            array(
                'subjectId' => $subjectId,
                'form' => $form->createView(),
                'workspace' => $subject->getForum()->getWorkspace()
            )
        );
    }

    /**
     * @Route(
     *     "/options/edit",
     *     name="claro_forum_edit_options"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editForumOptionsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $forumOptions = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $form = $this->container->get('form.factory')->create(new ForumOptionsType(), $forumOptions[0]);
        $form->bindRequest($this->get('request'));

        if ($form->isValid()) {
            $forumOptions = $form->getData();
            $em->persist($forumOptions);
            $em->flush();

            return new RedirectResponse($this->generateUrl('claro_admin_plugins'));
        }

        return $this->render(
            'ClarolineForumBundle::plugin_options_form.html.twig',
            array('form' => $form->createView())
        );
    }

    private function checkAccess($forum)
    {
        $collection = new ResourceCollection(array($forum));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}