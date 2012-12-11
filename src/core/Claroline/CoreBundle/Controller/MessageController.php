<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;
use Claroline\CoreBundle\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    const MESSAGE_PER_PAGE = 20;

    public function formForGroupAction($groupId)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $users = $em->getRepository('Claroline\CoreBundle\Entity\User')->usersOfGroup($groupId);
        $urlParameters = '?';

        $i=0;
        foreach ($users as $user){
            if ($i > 0) {
                $urlParameters.="&";
            }

            $urlParameters.="ids[]={$user->getId()}";

            $i++;
        }

        return $this->redirect($this->generateUrl('claro_message_form').$urlParameters);
    }

    public function formAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $params = $this->get('request')->query->all();
        $usersString = '';

        if (isset($params['ids'])) {
            foreach ($params['ids'] as $id){
                $user = $em->getRepository('ClarolineCoreBundle:User')->find($id);
                $usersString.= "{$user->getUsername()}; ";
            }
        }

        $form = $this->createForm(new MessageType($usersString));

        return $this->render(
            'ClarolineCoreBundle:Message:message_form.html.twig',
            array('form' => $form->createView())
        );
    }

    public function sendAction($parentId)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new MessageType(), new Message());
        $form->bindRequest($request);

         if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $message = $form->getData();
            $usernamesNotFound = array();
            $message->setUser($user);
            $parent = $em->getRepository('ClarolineCoreBundle:Message')->find($parentId);

            if ($parent != null) {
                $message->setParent($parent);
            }

            $to = str_replace(' ', '', $message->getTo());
            $usernames = explode(';', $to);

            foreach($usernames as $username){
                $user = $em->getRepository('ClarolineCoreBundle:user')->findOneBy(array('username' => $username));

                if ($user != null){
                    $userMessage = new UserMessage();
                    $userMessage->setUser($user);
                    $userMessage->setMessage($message);
                    $em->persist($userMessage);
                    $em->persist($message);
                } else {
                    $usernamesNotFound[] = $username;
                }
            }

            $em->flush();
            $form = $this->createForm(new MessageType());

            return $this->render(
                    'ClarolineCoreBundle:Message:message_form.html.twig', array('form' => $form->createView())
            );

         } else {
            // add success/error message...

            return $this->render(
                'ClarolineCoreBundle:Message:message_form.html.twig',
                array('form' => $form->createView())
            );
         }
    }

    public function listReceivedLayoutAction()
    {
        return $this->render(
            'ClarolineCoreBundle:Message:list_received_layout.html.twig'
        );
    }

    public function listSentLayoutAction()
    {
        return $this->render(
            'ClarolineCoreBundle:Message:list_sent_layout.html.twig'
        );
    }

    public function listReceivedAction($offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $userMessages = $em->getRepository('ClarolineCoreBundle:Message')->getUserReceivedMessages($this->get('security.context')->getToken()->getUser(), false, $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_user_message.html.twig', array('userMessages' => $userMessages)
        );
    }

    public function listSentAction($offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $messages = $em->getRepository('ClarolineCoreBundle:Message')->getSentMessages($this->get('security.context')->getToken()->getUser(), false, $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_message.html.twig', array('messages' => $messages)
        );
    }

    public function listRemovedLayoutAction()
    {
        return $this->render(
            'ClarolineCoreBundle:Message:list_removed_layout.html.twig'
        );
    }

    public function showAction($messageId)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $message = $em->getRepository('ClarolineCoreBundle:Message')->find($messageId);
        $userMessage = $em->getRepository('ClarolineCoreBundle:UserMessage')->findOneBy(array('message' => $message, 'user' => $this->get('security.context')->getToken()->getUser()));
        $ancestors =  $em->getRepository('ClarolineCoreBundle:Message')->getAncestors($message);

        if ($userMessage != null){
        //was received by the current user
            $form = $this->createForm(new MessageType($userMessage->getMessage()->getUser()->getUsername(), 'Re: '.$message->getObject(), true));
            $userMessage->markAsRead();
            $em->persist($userMessage);
            $em->flush();
        } else {
        //was sent by the current user
            $userMessages = $message->getUserMessages();
            $stringUsername = '';

            foreach($userMessages as $userMessage){
                 $stringUsername.= "{$userMessage->getUser()->getUsername()}; ";
            }

            $form = $this->createForm(new MessageType($stringUsername, 'Re: '.$message->getObject()));
        }

        return $this->render(
            'ClarolineCoreBundle:Message:show.html.twig', array('ancestors' => $ancestors, 'message' => $message, 'form' => $form->createView())
        );
    }

    public function deleteFromUserAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])){
            $em = $this->get('doctrine.orm.entity_manager');
            foreach($params['ids'] as $id){
                $message = $em->getRepository('Claroline\CoreBundle\Entity\Message')->find($id);
                $message->markAsRemoved();
                $em->persist($message);
            }
            $em->flush();
        }

        return new Response ('success', 204);
    }

    public function deleteToUserAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])){
            $em = $this->get('doctrine.orm.entity_manager');
            foreach($params['ids'] as $id){
                $userMessage = $em->getRepository('Claroline\CoreBundle\Entity\UserMessage')->find($id);
                $userMessage->markAsRemoved();
                $em->persist($userMessage);
            }
            $em->flush();
        }

        return new Response ('success', 204);
    }

    public function listSearchReceivedAction($search, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $userMessages = $em->getRepository('ClarolineCoreBundle:Message')->searchUserReceivedMessages($search, $this->get('security.context')->getToken()->getUser(), false, $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_user_message.html.twig', array('userMessages' => $userMessages)
        );
    }

    public function listSearchSentAction($search, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $messages = $em->getRepository('ClarolineCoreBundle:Message')->searchSentMessages($search, $this->get('security.context')->getToken()->getUser(), false, $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_message.html.twig', array('messages' => $messages)
        );
    }

    public function listRemovedAction($offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $userMessages = $em->getRepository('ClarolineCoreBundle:Message')->getRemovedMessages($this->get('security.context')->getToken()->getUser(), $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_user_removed_message.html.twig', array('userMessages' => $userMessages)
        );
    }

    public function listRemovedSearchAction($search, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $userMessages = $em->getRepository('ClarolineCoreBundle:Message')->searchRemovedMessages($search, $this->get('security.context')->getToken()->getUser(), $offset, self::MESSAGE_PER_PAGE);

        return $this->render(
            'ClarolineCoreBundle:Message:list_user_removed_message.html.twig', array('userMessages' => $userMessages)
        );
    }
}
