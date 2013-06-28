<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\MessageType;
use Claroline\CoreBundle\Manager\MessageManager;

class MessageController
{
    private $request;
    private $router;
    private $formFactory;
    private $messageManager;

    /**
     * @DI\InjectParams({
     *     "request"        = @DI\Inject("request"),
     *     "urlGenerator"   = @DI\Inject("router"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "manager"        = @DI\Inject("claroline.manager.message_manager")
     * })
     */
    public function __construct(
        Request $request,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        MessageManager $manager
    )
    {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->messageManager = $manager;
    }

    /**
     * @EXT\Route(
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
        $url = $this->router->generate('claro_message_form')
            . $this->messageManager->generateGroupQueryString($group);

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/form",
     *     name="claro_message_form"
     * )
     * @EXT\Template("ClarolineCoreBundle:Message:messageForm.html.twig")
     * @EXT\ParamConverter("receivers", class="ClarolineCoreBundle:User", options={"multipleIds" = true})
     *
     * Display the message form.
     * It takes a array of user ids (query string: ids[]=1&ids[]=2).
     * The "to" field of the form must be completed in the following way: username1; username2; username3
     * (the separator is ; and it requires the username).
     *
     * @return Response
     */
    public function formAction(array $receivers)
    {
        $usersString = $this->messageManager->generateStringTo($receivers);
        $form = $this->formFactory->create(new MessageType($usersString));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/send/{parent_id}",
     *     name="claro_message_send",
     *     defaults={"parent_id" = null}
     * )
     * @EXT\Method({"POST"})
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "parent",
     *     class="ClarolineCoreBundle:Message",
     *     options={"id" = "parent_id", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Message:messageForm.html.twig")
     *
     * Handles the message form submission.
     *
     * @return Response
     */
    public function sendAction(User $sender, Message $parent = null)
    {
        $msg =  new Message();
        $form = $this->formFactory->create(new MessageType(), $msg);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->messageManager->send($sender, $msg, $parent);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/received/page/{page}",
     *     name="claro_message_list_received",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/received/page/{page}/search/{search}",
     *     name="claro_message_list_received_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("receiver", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays received message list.
     *
     * @return Response
     */
    public function listReceivedAction(User $receiver, $page, $search)
    {
        $pager = $this->messageManager->getReceivedMessages($receiver, $search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/sent/page/{page}",
     *     name="claro_message_list_sent",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/sent/page/{page}/search/{search}",
     *     name="claro_message_list_sent_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the layout of the sent message list.
     *
     * @return Response
     */
    public function listSentAction(User $sender, $page, $search)
    {
        $pager = $this->messageManager->getSentMessages($sender, $search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/removed/page/{page}",
     *     name="claro_message_list_removed",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/removed/page/{page}/search/{search}",
     *     name="claro_message_list_removed_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     *
     * Displays the removed messages list.
     *
     * @return Response
     */
    public function listRemovedAction(User $user, $page, $search)
    {
        $pager = $this->messageManager->getSentMessages($user, $search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/show/{message}",
     *     name="claro_message_show"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays a message.
     *
     * @param integer $messageId the message id
     *
     * @return Response
     */
    public function showAction(User $user, Message $message)
    {
        $this->messageManager->markAsRead($user, array($message));
        $ancestors = $this->messageManager->getConversation($message);
        $form = $this->formFactory->create(new MessageType($message->getSenderUsername(), 'Re: ' . $message->getObject()));

        return array(
            'ancestors' => $ancestors,
            'message' => $message,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/delete/from",
     *     name="claro_message_delete_from",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Deletes a message from the sent message list (soft delete).
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteFromUserAction(User $user, array $messages)
    {
        $this->messageManager->markAsRemoved($user, $messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/delete/to",
     *     name="claro_message_delete_to",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Deletes a message from the received message list (soft delete).
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteToUserAction(User $user, array $messages)
    {
        // SAME IMPLEMENTATION THAN THE PREVIOUS METHOD ???
        $this->messageManager->markAsRemoved($user, $messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/delete/trash",
     *     name="claro_message_delete_trash",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Deletes a message from trash (permanent delete).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteTrashAction(User $user, array $messages)
    {
        $this->messageManager->remove($user, $messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/restore",
     *     name="claro_message_restore_from_trash",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Restore a message from the trash.
     * It takes an array of ids in the query string (ids[]=1&ids[]=2).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restoreFromTrashAction(User $user, array $messages)
    {
        $this->messageManager->markAsUnremoved($user, $messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/mark_as_read/{message}",
     *     name="claro_message_mark_as_read",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Marks a message as read.
     *
     * @param integer $userMessageId the userMessage id (when you send a message,
     * a UserMessage is created for every user the message was sent. It contains
     * a few attributes including the "asRead" one.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markAsReadAction(User $user, Message $message)
    {
        $this->messageManager->markAsRead($user, array($message));

        return new Response('Success', 204);
    }
}