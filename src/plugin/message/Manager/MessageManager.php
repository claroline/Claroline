<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Repository\User\GroupRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;

class MessageManager
{
    /** @var MailManager */
    private $mailManager;
    /** @var ObjectManager */
    private $om;

    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    public function __construct(
        MailManager $mailManager,
        ObjectManager $om
    ) {
        $this->mailManager = $mailManager;
        $this->om = $om;

        $this->groupRepo = $om->getRepository(Group::class);
        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    /**
     * @return array - the list of users to which the message has really been sent
     */
    public function send(Message $message): array
    {
        if ($message->getSender()) {
            $userMessage = new UserMessage();
            $userMessage->setIsSent(true);
            $userMessage->setUser($message->getSender());
            $userMessage->setMessage($message);

            $this->om->persist($userMessage);
        }

        /** @var User[] $userReceivers */
        $userReceivers = [];

        $receivers = $message->getReceivers();
        if (count($receivers['users']) > 0) {
            $userReceivers = $this->userRepo->findByUsernames($receivers['users']);
        }

        if (count($receivers['groups']) > 0) {
            /** @var Group[] $groupReceivers */
            $groupReceivers = $this->groupRepo->findByNames($receivers['groups']);
            foreach ($groupReceivers as $groupReceiver) {
                $userReceivers = array_merge($userReceivers, $this->userRepo->findByGroup($groupReceiver));
            }
        }

        if (count($receivers['workspaces']) > 0) {
            /** @var Workspace[] $workspaceReceivers */
            $workspaceReceivers = $this->workspaceRepo->findByCodes($receivers['workspaces']);
            if (!empty($workspaceReceivers)) {
                $userReceivers = array_merge($userReceivers, $this->userRepo->findByWorkspaces($workspaceReceivers));
            }
        }

        $ids = [];
        $filteredUsers = array_filter($userReceivers, function (User $user) use (&$ids) {
            if (!$user->isEnabled() || $user->isRemoved() || !$user->isAccountNonExpired()) {
                // never send messages to disabled users (we might need to manage it in repos instead)
                return false;
            }

            // deduplicate list of users
            if (!in_array($user->getId(), $ids)) {
                $ids[] = $user->getId();

                return true;
            }

            return false;
        });

        // also send message by email
        // TODO : subscribe to `SendMessageEvent` instead
        $mailNotifiedUsers = [];
        foreach ($filteredUsers as $filteredUser) {
            $userMessage = new UserMessage();
            $userMessage->setUser($filteredUser);
            $userMessage->setMessage($message);
            $this->om->persist($userMessage);

            if ($filteredUser->isMailNotified()) {
                $mailNotifiedUsers[] = $filteredUser;
            }
        }

        if (!empty($mailNotifiedUsers)) {
            $replyToMail = !empty($message->getSender()) ? $message->getSender()->getEmail() : null;

            $extra = [];
            if (!empty($message->getAttachments())) {
                $extra['attachments'] = $message->getAttachments();
            }

            $this->mailManager->send(
                $message->getObject(),
                $message->getContent(),
                $mailNotifiedUsers,
                $message->getSender(),
                $extra,
                false,
                $replyToMail
            );
        }

        $this->om->persist($message);
        $this->om->flush();

        return $filteredUsers;
    }

    /**
     * @param AbstractRoleSubject[] $receivers
     */
    public function sendMessage(
        $content,
        $object,
        array $receivers = null,
        ?User $sender = null,
        array $attachments = []
    ) {
        $users = [];
        foreach ($receivers as $receiver) {
            if ($receiver instanceof User) {
                $users[] = $receiver;
            } elseif ($receiver instanceof Group) {
                $users = array_merge($users, $receiver->getUsers()->toArray());
            }
        }

        $message = $this->create($content, $object, $users, $sender, null, $attachments);

        return $this->send($message);
    }

    public function remove(UserMessage $message)
    {
        $this->om->remove($message);
        $this->om->flush();
    }

    /**
     * Create a message.
     *
     * @param string                $content The message content
     * @param string                $object  The message object
     * @param AbstractRoleSubject[] $users   The users receiving the message
     * @param null                  $sender  The user sending the message
     * @param null                  $parent  The message parent (is it's a discussion)
     *
     * @return Message
     */
    private function create($content, $object, array $users, $sender = null, $parent = null, array $attachments = [])
    {
        $message = new Message();

        $message->setContent($content);
        $message->setParent($parent);
        $message->setObject($object);
        $message->setSender($sender);
        $message->setAttachments($attachments);

        $message->setReceivers(array_map(function (User $user) {
            return $user->getUsername();
        }, $users));

        return $message;
    }
}
