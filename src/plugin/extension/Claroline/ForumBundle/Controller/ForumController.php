<?php

namespace Claroline\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Form\ForumType;


/**
 * ForumController
 */
class ForumController extends Controller
{
    public function getResourceType()
    {
        return 'Forum';
    }

    public function getForm()
    {
        return $this->get('form.factory')->create(new ForumType, new Forum());
    }

    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->get('form.factory')->create(new ForumType, new Forum());

        return $this->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $id, 'type' => $type)
        );
    }

    public function add($form, $id, User $user)
    {
        $name = $form['name']->getData();
        $forum = new Forum();
        $forum->setName($name);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($forum);
        $em->flush();

        return $forum;
    }

    public function getDefaultAction($resourceId)
    {
        $forum = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\ForumBundle\Entity\Forum')->find($resourceId);
        $content = $this->render(
            'ClarolineForumBundle::index.html.twig', array('forum' => $forum)
        );

        $response = new Response($content);

        return $response;
    }

    public function forumSubjectCreationAction($forumId)
    {
        $formSubject = $this->get('form.factory')->create(new SubjectType());

        $content = $this->render(
            'ClarolineForumBundle::subject_form.html.twig', array('form' => $formSubject->createView(), 'forumId' => $forumId)
        );

        return new Response($content);
    }

    public function createSubjectAction($forumId)
    {
        $form = $this->get('form.factory')->create(new SubjectType());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $title = $form['title']->getData();
        $content = $form['content']->getData();
        $subjectType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'Subject'));
        $messageType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'Message'));
        $forum = $em->getRepository('Claroline\ForumBundle\Entity\Forum')->find($forumId);
        $message = new Message();
        $subject = new Subject();
        $message->setResourceType($messageType);
        $subject->setResourceType($subjectType);
        $subject->setTitle($title);
        $subject->setUser($user);
        $message->setContent($content);
        $subject->setForum($forum);
        $message->setSubject($subject);
        $message->setUser($user);
        $em->persist($message);
        $em->persist($subject);

        $em->flush();

        return new Response("hello world this is created");
    }

    public function showMessagesAction($subjectId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('Claroline\ForumBundle\Entity\Subject')->find($subjectId);

        return $this->render(
            'ClarolineForumBundle::messages.html.twig', array('subject' => $subject)
        );
    }

    public function forumMessageCreationFormAction($subjectId)
    {
        $form = $this->get('form.factory')->create(new MessageType());

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig', array('subjectId', $subjectId, 'form', $form)
        );
    }
}
