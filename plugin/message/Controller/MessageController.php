<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Form\MessageType;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class MessageController
{
    private $formFactory;
    private $groupManager;
    private $mailManager;
    private $messageManager;
    private $pagerFactory;
    private $request;
    private $router;
    private $tokenStorage;
    private $userManager;
    private $utils;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "mailManager"      = @DI\Inject("claroline.manager.mail_manager"),
     *     "messageManager"   = @DI\Inject("claroline.manager.message_manager"),
     *     "pagerFactory"     = @DI\Inject("claroline.pager.pager_factory"),
     *     "request"          = @DI\Inject("request"),
     *     "router"           = @DI\Inject("router"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "utils"            = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        GroupManager $groupManager,
        MailManager $mailManager,
        MessageManager $messageManager,
        PagerFactory $pagerFactory,
        Request $request,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        Utilities $utils,
        WorkspaceManager $workspaceManager
    ) {
        $this->formFactory = $formFactory;
        $this->groupManager = $groupManager;
        $this->mailManager = $mailManager;
        $this->messageManager = $messageManager;
        $this->pagerFactory = $pagerFactory;
        $this->request = $request;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/form/user/{user}",
     *     name="claro_message_form_for_user",
     *     options={"expose"=true}
     * )
     *
     * Displays the message form with the "to" field filled with user.
     *
     * @param User $user
     *
     * @return Response
     */
    public function formForUserAction(User $user)
    {
        $url = $this->router->generate('claro_message_show', ['message' => 0])
            .'?userIds[]='.$user->getId();

        return new RedirectResponse($url);
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
        $url = $this->router->generate('claro_message_show', ['message' => 0])
            .'?grpsIds[]='.$group->getId();

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/form/workspace/{workspace}",
     *     name="claro_message_form_for_workspace"
     * )
     *
     * Displays the message form with the "to" field filled with users of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function formForWorkspaceAction(Workspace $workspace)
    {
        $url = $this->router->generate('claro_message_show', ['message' => 0])
            .'?wsIds[]='.$workspace->getId();

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/send/{parentId}",
     *     name="claro_message_send",
     *     defaults={"parentId" = 0}
     * )
     * @EXT\Method({"POST"})
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "parent",
     *     class="ClarolineMessageBundle:Message",
     *     options={"id" = "parentId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineMessageBundle:Message:show.html.twig")
     *
     * Handles the message form submission.
     *
     * @param User    $sender
     * @param Message $parent
     *
     * @return Response
     */
    public function sendAction(User $sender, Message $parent = null)
    {
        $form = $this->formFactory->create(new MessageType(), new Message());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $message = $form->getData();
            $message->setSender($sender);
            $message->setParent($parent);
            $message = $this->messageManager->send($message);
            $url = $this->router->generate('claro_message_show', ['message' => $message->getId()]);

            return new RedirectResponse($url);
        }

        $ancestors = $parent ? $this->messageManager->getConversation($parent, $sender) : [];

        return ['form' => $form->createView(), 'message' => $parent, 'ancestors' => $ancestors];
    }

    /**
     * @EXT\Route(
     *     "/received/page/{page}",
     *     name="claro_message_list_received",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/received/page/{page}/search/{search}",
     *     name="claro_message_list_received_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("receiver", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the messages received by a user, optionally filtered by a search
     * on the object or the sender username.
     *
     * @param User   $receiver
     * @param int    $page
     * @param string $search
     *
     * @return Response
     */
    public function listReceivedAction(User $receiver, $page, $search)
    {
        $pager = $this->messageManager->getReceivedMessagesPager($receiver, $search, $page);

        return [
            'pager' => $pager,
            'search' => $search,
            'isMailerAvailable' => $this->mailManager->isMailerAvailable(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/sent/page/{page}",
     *     name="claro_message_list_sent",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/sent/page/{page}/search/{search}",
     *     name="claro_message_list_sent_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("sender", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the messages sent by a user, optionally filtered by a search
     * on the object.
     *
     * @param User   $sender
     * @param int    $page
     * @param string $search
     *
     * @return Response
     */
    public function listSentAction(User $sender, $page, $search)
    {
        $pager = $this->messageManager->getSentMessagesPager($sender, $search, $page);

        return ['pager' => $pager, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/removed/page/{page}",
     *     name="claro_message_list_removed",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/removed/page/{page}/search/{search}",
     *     name="claro_message_list_removed_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param User   $user
     * @param int    $page
     * @param string $search
     *
     * @return Response
     */
    public function listRemovedAction(User $user, $page, $search)
    {
        $pager = $this->messageManager->getRemovedMessagesPager($user, $search, $page);

        return ['pager' => $pager, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/show/{message}",
     *     name="claro_message_show",
     *     defaults={"message"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "receivers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     * @EXT\ParamConverter(
     *      "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name"="wsIds"}
     * )
     * @EXT\ParamConverter(
     *      "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name"="grpsIds"}
     * )
     * @EXT\ParamConverter(
     *      "message",
     *      class="ClarolineMessageBundle:Message",
     *      options={"id" = "message", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays a message.
     *
     * @param User    $user
     * @param array   $receivers
     * @param array   $groups
     * @param array   $workspaces
     * @param Message $message
     *
     * @return Response
     */
    public function showAction(
        User $user,
        array $receivers,
        array $groups,
        array $workspaces,
        Message $message = null
    ) {
        if ($message) {
            $this->messageManager->markAsRead($user, [$message]);
            $ancestors = $this->messageManager->getConversation($message, $user);
            $sendString = $message->getSenderUsername();
            $object = 'Re: '.$message->getObject();
            $this->checkAccess($message, $user);
        } else {
            //datas from the post request
            $sendString = $this->messageManager->generateStringTo($receivers, $groups, $workspaces);
            $object = '';
            $ancestors = [];
        }
        $form = $this->formFactory->create(
            new MessageType($sendString, $object),
            new Message()
        );

        return [
            'ancestors' => $ancestors,
            'message' => $message,
            'form' => $form->createView(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/remove",
     *     name="claro_message_soft_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "messages",
     *     class="ClarolineMessageBundle:UserMessage",
     *     options={"multipleIds" = true}
     * )
     *
     * Moves messages from the list of sent or received messages to the trash bin.
     *
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function softDeleteAction(array $messages)
    {
        $this->messageManager->markAsRemoved($messages);

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
     * @EXT\ParamConverter(
     *     "messages",
     *     class="ClarolineMessageBundle:UserMessage",
     *     options={"multipleIds" = true}
     * )
     *
     * Deletes permanently a set of messages received or sent by a user.
     *
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function deleteAction(array $messages)
    {
        $this->messageManager->remove($messages);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/restore",
     *     name="claro_message_restore_from_trash",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "messages",
     *     class="ClarolineMessageBundle:UserMessage",
     *     options={"multipleIds" = true}
     * )
     *
     * Restores messages previously moved to the trash bin.
     *
     * @param array[Message] $messages
     *
     * @return Response
     */
    public function restoreFromTrashAction(array $messages)
    {
        $this->messageManager->markAsUnremoved($messages);

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
        $this->messageManager->markAsRead($user, [$message]);

        return new Response('Success', 204);
    }

    /**
     * @EXT\Route(
     *     "/contactable/users/page/{page}",
     *     name="claro_message_contactable_users",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/contactable/users/page/{page}/search/{search}",
     *     name="claro_message_contactable_users_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param int    $page
     * @param string $search
     * @param User   $user
     *
     * @return Response
     */
    public function contactableUsersListAction(User $user, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($user->hasRole('ROLE_ADMIN')) {
            if ($trimmedSearch === '') {
                $users = $this->userManager->getAllUsers($page);
            } else {
                $users = $this->userManager
                    ->getAllUsersBySearch($page, $trimmedSearch);
            }
        } else {
            $users = [];
            $token = $this->tokenStorage->getToken();
            $roles = $this->utils->getRoles($token);
            $workspaces = $this->workspaceManager->getOpenableWorkspacesByRoles($roles);

            if (count($workspaces) > 0) {
                if ($trimmedSearch === '') {
                    $users = $this->userManager
                        ->getUsersByWorkspaces($workspaces, $page);
                } else {
                    $users = $this->userManager->getUsersByWorkspacesAndSearch(
                        $workspaces,
                        $page,
                        $search
                    );
                }
            }
        }

        return ['users' => $users, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/notification/{isNotified}",
     *     name="claro_message_notification",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param bool $isNotified
     * @param User $user
     *
     * @return Response
     */
    public function setMailNotificationAction($isNotified, User $user)
    {
        $this->userManager->setIsMailNotified($user, $isNotified);

        return new JsonResponse(['success' => 'success']);
    }

    /**
     * @EXT\Route(
     *     "/contactable/groups/page/{page}",
     *     name="claro_message_contactable_groups",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/contactable/groups/page/{page}/search/{search}",
     *     name="claro_message_contactable_groups_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param int    $page
     * @param string $search
     * @param User   $user
     *
     * @return Response
     */
    public function contactableGroupsListAction(User $user, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($user->hasRole('ROLE_ADMIN')) {
            if ($trimmedSearch === '') {
                $groups = $this->groupManager->getAllGroups($page);
            } else {
                $groups = $this->groupManager
                    ->getAllGroupsBySearch($page, $trimmedSearch);
            }
        } else {
            $groups = [];
            $workspaces = $this->workspaceManager
                ->getWorkspacesByUserAndRoleNames($user, ['ROLE_WS_MANAGER']);
            // retrieve all groups of workspace that user is manager
            if (count($workspaces) > 0) {
                if ($trimmedSearch === '') {
                    $groups = $this->groupManager
                        ->getGroupsByWorkspaces($workspaces);
                } else {
                    $groups = $this->groupManager->getGroupsByWorkspacesAndSearch(
                        $workspaces,
                        $search
                    );
                }
            }

            // get groups in which user is subscribed
            $userGroups = $user->getGroups();
            $userGroupsFinal = [];

            if ($trimmedSearch === '') {
                $userGroupsFinal = $userGroups;
            } else {
                $upperSearch = strtoupper($trimmedSearch);

                foreach ($userGroups as $userGroup) {
                    $upperName = strtoupper($userGroup->getName());

                    if (strpos($upperName, $upperSearch) !== false) {
                        $userGroupsFinal[] = $userGroup;
                    }
                }
            }

            // merge the 2 groups array
            foreach ($userGroupsFinal as $userGroupFinal) {
                if (!in_array($userGroupFinal, $groups, true)) {
                    $groups[] = $userGroupFinal;
                }
            }

            $groups = $this->pagerFactory->createPagerFromArray($groups, $page);
        }

        return ['groups' => $groups, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/contactable/workspaces/page/{page}",
     *     name="claro_message_contactable_workspaces",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Route(
     *     "/contactable/workspaces/page/{page}/search/{search}",
     *     name="claro_message_contactable_workspaces_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param int    $page
     * @param string $search
     * @param User   $user
     *
     * @return Response
     */
    public function contactableWorkspacesListAction(User $user, $page, $search)
    {
        $workspaces = $this->workspaceManager->getWorkspacesByManager($user);
        $pager = $this->pagerFactory->createPagerFromArray($workspaces, $page);

        return ['workspaces' => $pager, 'search' => $search];
    }

    public function checkAccess(Message $message, User $user)
    {
        if ($message->getSenderUsername() === $user->getUsername()) {
            return true;
        }
        $userMessage = $this->messageManager
            ->getOneUserMessageByUserAndMessage($user, $message);

        if (!is_null($userMessage)) {
            return true;
        }

        $receiverString = $message->getTo();
        $names = explode(';', $receiverString);
        $usernames = [];
        $groupNames = [];
        $workspaceCodes = [];

        foreach ($names as $name) {
            if (substr($name, 0, 1) === '{') {
                $groupNames[] = trim($name, '{}');
            } elseif (substr($name, 0, 1) === '[') {
                $workspaceCodes[] = trim($name, '[]');
            } else {
                $usernames[] = trim($name);
            }
        }

        if (in_array($user->getUsername(), $usernames)) {
            return true;
        }

        foreach ($user->getGroups() as $group) {
            if (in_array($group->getName(), $groupNames)) {
                return true;
            }
        }

        foreach ($this->workspaceManager->getWorkspacesByUser($user) as $workspace) {
            if (in_array($workspace->getCode(), $workspaceCodes)) {
                return true;
            }
        }

        throw new AccessDeniedException();
    }
}
