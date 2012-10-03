<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * ForumController
 */
class ForumController extends Controller
{
    public function OpenAction($instanceId)
    {
        $instance = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $content = $this->render(
            'ClarolineForumBundle::index.html.twig', array('forumInstance' => $instance, 'workspace' => $instance->getWorkspace())
        );

        $response = new Response($content);

        return $response;
    }

    public function forumSubjectCreationFormAction($forumInstanceId)
    {
        $formSubject = $this->get('form.factory')->create(new SubjectType());

        $content = $this->render(
            'ClarolineForumBundle::subject_form.html.twig', array('form' => $formSubject->createView(), 'forumInstanceId' => $forumInstanceId)
        );

        return new Response($content);
    }

    public function createSubjectAction($forumInstanceId)
    {
        $form = $this->get('form.factory')->create(new SubjectType());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $title = $form['title']->getData();
        $content = $form['content']->getData();
        $forumInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($forumInstanceId);
        $message = new Message();
        $subject = new Subject();
        $subject->setTitle($title);
        $subject->setCreator($user);
        $message->setContent($content);
        $message->setCreator($user);
        $subject->setForum($forumInstance->getResource());
        $message->setSubject($subject);
        $message->setName('testmsg');
        $subject->setName('testsub');
        $em->persist($message);
        $em->persist($subject);
        $em->flush();
        $creator = $this->get('claroline.resource.manager');
        $subjectInstance = $creator->create($subject, $forumInstanceId, 'Subject');
        $creator->create($message, $subjectInstance->getId(), 'Message');

        return new RedirectResponse($this->generateUrl('claro_forum_open', array('instanceId' => $forumInstanceId)));
    }

    public function showMessagesAction($subjectId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('Claroline\ForumBundle\Entity\Subject')->find($subjectId);

        return $this->render(
            'ClarolineForumBundle::messages.html.twig', array('subject' => $subject)
        );
    }

    public function messageCreationFormAction($subjectId)
    {
        $form = $this->get('form.factory')->create(new MessageType());

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig', array('subjectId' => $subjectId, 'form' =>  $form->createView())
        );
    }

    public function createMessageAction($subjectId)
    {
        $form = $this->get('form.factory')->create(new MessageType());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $subject = $em->getRepository('Claroline\ForumBundle\Entity\Subject')->find($subjectId);
        $user = $this->get('security.context')->getToken()->getUser();
        $messageType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'Message'));
        $message = new Message();
        $content = $form['content']->getData();
        $message->setSubject($subject);
        $message->setCreator($user);
        $message->setResourceType($messageType);
        $message->setContent($content);
        $em->persist($message);;
        $em->flush();

        return new RedirectResponse($this->generateUrl('claro_forum_show_message', array('subjectId' => $subjectId)));
    }
}
