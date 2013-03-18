<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormError;

/**
 * ForumController
 */
class ForumController extends Controller
{
    public function openAction($resourceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($resourceId);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getSubjects();
        $countSubjects = $em->getRepository('ClarolineForumBundle:Forum')->countSubjectsForForum($forum);
        $nbPages = ceil($countSubjects / $limit);

        return $this->render(
            'ClarolineForumBundle::index.html.twig',
            array(
                'forum' => $forum,
                'workspace' => $forum->getWorkspace(),
                'limit' => $limit,
                'nbPages' => $nbPages
            )
        );
    }

    public function subjectsAction($forumId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($forumId);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getSubjects();
        $subjects = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($forum, $offset, $limit);

        return $this->render(
            'ClarolineForumBundle::subjects.html.twig', array('subjects' => $subjects)
        );
    }

    public function forumSubjectCreationFormAction($forumId)
    {
        $formSubject = $this->get('form.factory')->create(new SubjectType());
        $workspace = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
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

    public function createSubjectAction($forumId)
    {
        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $forum = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($forumId);

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
                    $this->generateUrl('claro_forum_open', array('resourceId' => $forum->getId()))
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

    public function showMessagesAction($subjectId)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $countMessages = $em->getRepository('ClarolineForumBundle:Forum')
            ->countMessagesForSubject($subject);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getMessages();
        $nbPages = ceil($countMessages / $limit);
        $workspace = $subject->getForum()->getWorkspace();

        return $this->render(
            'ClarolineForumBundle::messages_table.html.twig',
            array(
                'subject' => $subject,
                'workspace' => $workspace,
                'limit' => $limit,
                'nbPages' => $nbPages
            )
        );
    }

    public function messageCreationFormAction($subjectId)
    {
        $form = $this->get('form.factory')->create(new MessageType());
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig',
            array(
                'subjectId' => $subjectId,
                'form' => $form->createView(),
                'workspace' => $subject->getForum()->getWorkspace()
            )
        );
    }

    public function messagesAction($subjectId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getMessages();
        $messages = $em->getRepository('ClarolineForumBundle:Message')
            ->findBySubject($subject, $offset, $limit);

        return $this->render(
            'ClarolineForumBundle::messages.html.twig', array('messages' => $messages)
        );
    }

    public function createMessageAction($subjectId)
    {
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);

        if ($form->isValid()) {
            $message = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $message->setCreator($user);
            $message->setSubject($subject);
            $em->persist($message);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('claro_forum_show_message', array('subjectId' => $subjectId))
            );
        }

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig',
            array(
                'subjectId' => $subjectId,
                'form' => $form->createView(),
                'workspace' => $subject->getWorkspace()
            )
        );
    }

    public function editForumOptionsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
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
}