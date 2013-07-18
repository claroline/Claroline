<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Manager\MessageManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;

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
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "manager"        = @DI\Inject("claroline.manager.message_manager")
     * })
     */
    public function __construct(
        Request $request,
        UrlGeneratorInterface $router,
        FormFactory $formFactory,
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
     *     "/form",
     *     name="claro_message_form"
     * )
     * @EXT\Template("ClarolineCoreBundle:Message:messageForm.html.twig")
     * @EXT\ParamConverter("receivers", class="ClarolineCoreBundle:User", options={"multipleIds" = true})
     *
     * Displays the message form with the "to" field filled with users.
     *
     * @param array[User] $receivers
     *
     * @return Response
     */
    public function formAction(array $receivers)
    {
        $usersString = $this->messageManager->generateStringTo($receivers);
        $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE, array($usersString));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/form/group/{group}",
     *     name="claro_message_form_for_group"
     * )
     *
     * Displays the message form with the "to" field filled with users of a group.
     *
     * @param Group $group
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
        $form = $this->formFactory->create(FormFactory::TYPE_MESSAGE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->messageManager->send($sender, $form->getData(), $parent);
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
     * Displays the messages received by a user, optionally filtered by a search
     * on the object or the sender username.
     *
     * @param User    $receiver
     * @param integer $page
     * @param string  $search
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
     * Displays the messages sent by a user, optionally filtered by a search
     * on the object.
     *
     * @param User    $sender
     * @param integer $page
     * @param string  $search
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
     * Displays the messages removed by a user, optionally filtered by a search
     * on the object or the sender username.
     *
     * @param User    $user
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function listRemovedAction(User $user, $page, $search)
    {
        $pager = $this->messageManager->getRemovedMessages($user, $search, $page);

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
        $form = $this->formFactory->create(
            FormFactory::TYPE_MESSAGE,
            array($message->getSenderUsername(), 'Re: ' . $message->getObject()),
            $message
        );

        return array(
            'ancestors' => $ancestors,
            'message' => $message,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/remove",
     *     name="claro_message_soft_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Moves messages from the list of sent or received messages to the trash bin.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function softDeleteAction(User $user, array $messages)
    {
        $this->messageManager->markAsRemoved($user, $messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="claro_message_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter("messages", class="ClarolineCoreBundle:Message", options={"multipleIds" = true})
     *
     * Deletes permanently a set of messages received or sent by a user.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function deleteAction(User $user, array $messages)
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
     * Restores messages previously moved to the trash bin.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return Response
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
     * @param User    $user
     * @param Message $message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markAsReadAction(User $user, Message $message)
    {
        $this->messageManager->markAsRead($user, array($message));

        return new Response('Success', 204);
    }
}
