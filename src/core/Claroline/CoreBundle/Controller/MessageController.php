<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\MessageType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MessageController extends Controller
{
    const MESSAGE_PER_PAGE = 20;

    /**
     * @Route(
     *     "/form/group/{group}",
     *     name="claro_message_form_for_group"
     * )
     *
     * Displays the message form. It'll be sent to every user of a group.
     * In order to do this, this methods redirects to the form creation controller
     * with a query string including every users of the group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function formForGroupAction(Group $group)
    {
        $qs = $this->get('claroline.manager.message_manager')->generateGroupQueryString($group);

        return $this->redirect($this->generateUrl('claro_message_form') . $qs);
    }

    /**
     * @Route(
     *     "/form",
     *     name="claro_message_form"
     * )
     *
     * @Template("ClarolineCoreBundle:Message:messageForm.html.twig")
     *
     * Display the message form.
     * It takes a array of user ids (query string: ids[]=1&ids[]=2).
     * The "to" field of the form must be completed in the following way: username1; username2; username3
     * (the separator is ; and it requires the username).
     *
     * @return Response
     */
    public function formAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $params = $this->get('request')->query->all();
        $usersString = '';

        if (isset($params['ids'])) {
            $usersString = $this->get('claroline.manager.message_manager')->generateStringTo($params['ids']);
        }

        $form = $this->createForm(new MessageType($usersString));

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/send/{parentId}",
     *     name="claro_message_send",
     *     defaults={"parentId" = 0}
     * )
     *
     * @Template("ClarolineCoreBundle:Message:messageForm.html.twig")
     *
     * Handles the message form submission.
     *
     * @return Response
     */
    public function sendAction($parentId)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new MessageType(), new Message());
        $form->handleRequest($request);
        $parent = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message')->find($parentId);

        if ($form->isValid()) {
            $this->get('claroline.manager.message_manager')->create(
                $user,
                $form->get('to')->getData(),
                $form->get('content')->getData(),
                $form->get('object')->getData(),
                $parent
            );

            return array('form' => $form->createView());
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @Route(
     *     "/received/page/{page}",
     *     name="claro_message_list_received",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     *
     * @Method("GET")
     *
     * @Route(
     *     "/received/page/{page}/search/{search}",
     *     name="claro_message_list_received_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     *
     * @Method("GET")
     *
     * @Template()
     *
     * Displays received message list.
     *
     * @return Response
     */
    public function listReceivedAction($page, $search)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message');
        $query = ($search == "") ?
            $repo->findReceivedByUser($user, false, true):
            $repo->findReceivedByUserAndObjectAndUsername($user, $search, false, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @Route(
     *     "/sent/page/{page}",
     *     name="claro_message_list_sent",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     *
     * @Method("GET")
     *
     * @Route(
     *     "/sent/page/{page}/search/{search}",
     *     name="claro_message_list_sent_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     *
     * @Method("GET")
     *
     * @Template()
     *
     *
     * Displays the layout of the sent message list.
     *
     * @return Response
     */
    public function listSentAction($page, $search)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message');
        $query = ($search == "") ?
            $repo->findSentByUser($user, false, true):
            $repo->findSentByUserAndObjectAndUsername($user, $search, false, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @Route(
     *     "/removed/page/{page}",
     *     name="claro_message_list_removed",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     *
     * @Method("GET")
     *
     * @Route(
     *     "/removed/page/{page}/search/{search}",
     *     name="claro_message_list_removed_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     *
     * @Method("GET")
     *
     * @Template()
     *
     *
     * Displays the layout of the sent message list.
     *
     * @return Response
     */
    public function listRemovedAction($page, $search)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message');
        $query = ($search == "") ?
            $repo->findRemovedByUser($user, true):
            $repo->findRemovedByUserAndObjectAndUsername($user, $search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @Route(
     *     "/show/{message}",
     *     name="claro_message_show"
     * )
     *
     * @Template()
     * 
     * Displays a message.
     *
     * @param integer $messageId the message id
     *
     * @return Response
     */
    public function showAction(Message $message)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $msgRepo = $em->getRepository('ClarolineCoreBundle:Message');
        $manager = $this->get('claroline.manager.message_manager');
        $manager->markAsRead($user, array($message));
        $ancestors = $msgRepo->findAncestors($message);
        $form = $this->createForm(new MessageType($message->getSenderUsername(), 'Re: ' . $message->getObject()));

        return array(
            'ancestors' => $ancestors,
            'message' => $message,
            'form' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/delete/from",
     *     name="claro_message_delete_from",
     *     options={"expose"=true}
     * )
     *
     * Deletes a message from the sent message list (soft delete).
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteFromUserAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->get('doctrine.orm.entity_manager');
            $messages = $em->getRepository('ClarolineCoreBundle:Message')->findByIds($params['ids']);
            $this->get('claroline.manager.message_manager')
                ->markAsRemoved($this->get('security.context')->getToken()->getUser(), $messages);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/delete/to",
     *     name="claro_message_delete_to",
     *     options={"expose"=true}
     * )
     *
     * Deletes a message from the received message list (soft delete).
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteToUserAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->get('doctrine.orm.entity_manager');
            $messages = $em->getRepository('ClarolineCoreBundle:Message')->findByIds($params['ids']);
            $this->get('claroline.manager.message_manager')
                ->markAsRemoved($this->get('security.context')->getToken()->getUser(), $messages);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/delete/trash",
     *     name="claro_message_delete_trash",
     *     options={"expose"=true}
     * )
     * @Method("DELETE")
     *
     * Deletes a message from trash (permanent delete).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteTrashAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->get('doctrine.orm.entity_manager');
            $messages = $em->getRepository('ClarolineCoreBundle:Message')->findByIds($params['ids']);
            $this->get('claroline.manager.message_manager')
                ->remove($this->get('security.context')->getToken()->getUser(), $messages);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/restore",
     *     name="claro_message_restore_from_trash",
     *     options={"expose"=true}
     * )
     *
     * Restore a message from the trash.
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restoreFromTrashAction()
    {
        $params = $this->get('request')->query->all();

        if (isset($params['ids'])) {
            $em = $this->get('doctrine.orm.entity_manager');
            $messages = $em->getRepository('ClarolineCoreBundle:Message')->findByIds($params['ids']);
            $this->get('claroline.manager.message_manager')
                ->markAsUnremoved($this->get('security.context')->getToken()->getUser(), $messages);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/mark_as_read/{message}",
     *     name="claro_message_mark_as_read",
     *     options={"expose"=true}
     * )
     *
     * Marks a message as read.
     *
     * @param integer $userMessageId the userMessage id (when you send a message,
     * a UserMessage is created for every user the message was sent. It contains
     * a few attributes including the "asRead" one.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markAsReadAction(Message $message)
    {
        $this->get('claroline.manager.message_manager')
            ->markAsRead($this->get('security.context')->getToken()->getUser(), array($message));

        return new Response('success', 203);
    }
}