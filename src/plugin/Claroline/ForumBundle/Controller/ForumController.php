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
        $subjects = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\ForumBundle\Entity\Forum')->getSubjects($instance);
        $users = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\ForumBundle\Entity\Forum')->getLastUser($instance);

        $content = $this->render(
            'ClarolineForumBundle::index.html.twig', array('forumInstance' => $instance, 'workspace' => $instance->getWorkspace(), 'subjects' => $subjects)
        );

        $response = new Response($content);

        return $response;
    }

    public function forumSubjectCreationFormAction($forumInstanceId)
    {
        $formSubject = $this->get('form.factory')->create(new SubjectType());
        $workspace = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($forumInstanceId)->getWorkspace();
        $content = $this->render(
            'ClarolineForumBundle::subject_form.html.twig', array('form' => $formSubject->createView(), 'forumInstanceId' => $forumInstanceId, 'workspace' => $workspace)
        );

        return new Response($content);
    }

    /*
     * The form submission is working but I had to do some weird things to make it works.
     */

    public function createSubjectAction($forumInstanceId)
    {
        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $forumInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($forumInstanceId);

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $subject = $form->getData();
            $dataMessage = $subject->getMessages();
            $message = new Message();
            $message->setContent($dataMessage['content']);
            $subject->setCreator($user);
            $message->setCreator($user);
            $subject->setForum($forumInstance->getResource());
            $message->setName($subject->getTitle() . '-' . date('m/d/Y h:i:m'));
            $subject->setName($subject->getTitle());
            $subject->resetMessages();
            $message->setSubject($subject);
            $em->persist($message);
            $em->persist($subject);
            $em->flush();
            $creator = $this->get('claroline.resource.manager');
            //instantiation of the new resources
            $subjectInstance = $creator->create($subject, $forumInstanceId, 'Subject');
            $creator->create($message, $subjectInstance->getId(), 'Message');

            return new RedirectResponse($this->generateUrl('claro_forum_open', array('instanceId' => $forumInstanceId)));
        } else {
            return $this->render(
                    'ClarolineForumBundle::subject_form.html.twig', array('form' => $form->createView(), 'forumInstanceId' => $forumInstanceId, 'workspace' => $forumInstance->getWorkspace())
            );
        }
    }

    public function showMessagesAction($subjectInstanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $subjectInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($subjectInstanceId);
        $workspace = $subjectInstance->getWorkspace();

        return $this->render(
            'ClarolineForumBundle::messages.html.twig', array('subjectInstance' => $subjectInstance, 'workspace' => $workspace)
        );
    }

    public function messageCreationFormAction($subjectInstanceId)
    {
        $form = $this->get('form.factory')->create(new MessageType());
        $em = $this->getDoctrine()->getEntityManager();
        $subjectInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($subjectInstanceId);

        return $this->render(
            'ClarolineForumBundle::message_form.html.twig', array('subjectInstanceId' => $subjectInstanceId, 'form' =>  $form->createView(), 'workspace' => $subjectInstance->getWorkspace())
        );
    }

    public function createMessageAction($subjectInstanceId)
    {
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->bindRequest($this->get('request'));
        $em = $this->getDoctrine()->getEntityManager();
        $subjectInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($subjectInstanceId);

        if ($form->isValid()) {
            $message = $form->getData();
            $subject = $subjectInstance->getResource();
            $user = $this->get('security.context')->getToken()->getUser();
            $messageType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'Message'));
            $message->setSubject($subject);
            $message->setCreator($user);
            $message->setResourceType($messageType);
            $creator = $this->get('claroline.resource.manager');
            $title = $subjectInstance->getParent()->getName();
            $message->setName($title . '-' . date('m/d/Y h:i:m'));
            $em->persist($message);
            $creator->create($message, $subjectInstance->getId(), 'Message');
            $em->flush();

            return new RedirectResponse($this->generateUrl('claro_forum_show_message', array('subjectInstanceId' => $subjectInstanceId)));
        } else {
            return $this->render(
                    'ClarolineForumBundle::message_form.html.twig', array('subjectInstanceId' => $subjectInstanceId, 'form' => $form->createView(), 'workspace' => $subjectInstance->getWorkspace())
            );
        }
    }
}
