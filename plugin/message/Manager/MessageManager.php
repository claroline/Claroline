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
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageManager
{
    /** @var MailManager */
    private $mailManager;
    /** @var ObjectManager */
    private $om;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * MessageManager constructor.
     *
     * @param MailManager           $mailManager
     * @param ObjectManager         $om
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        MailManager $mailManager,
        ObjectManager $om,
        TokenStorageInterface $tokenStorage
    ) {
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;

        $this->groupRepo = $om->getRepository(Group::class);
        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    /**
     * Create a message.
     *
     * @param string $content The message content
     * @param string $object  The message object
     * @param User[] $users   The users receiving the message
     * @param null   $sender  The user sending the message
     * @param null   $parent  The message parent (is it's a discussion)
     *
     * @return Message
     */
    public function create($content, $object, array $users, $sender = null, $parent = null)
    {
        $message = new Message();

        $message->setContent($content);
        $message->setParent($parent);
        $message->setObject($object);
        $message->setSender($sender);

        $message->setReceivers(array_map(function (User $user) {
            return $user->getUsername();
        }, $users));

        return $message;
    }

    /**
     * @param Message $message
     * @param bool    $setAsSent
     * @param bool    $sendMail
     *
     * @return Message
     */
    public function send(Message $message, $setAsSent = true, $sendMail = true)
    {
        /** @var User[] $userReceivers */
        $userReceivers = [];
        /** @var Group[] $groupReceivers */
        $groupReceivers = [];
        /** @var Workspace[] $workspaceReceivers */
        $workspaceReceivers = [];

        $receivers = $message->getReceivers();
        if (count($receivers['users']) > 0) {
            $userReceivers = $this->userRepo->findByUsernames($receivers['users']);
        }

        if (count($receivers['groups']) > 0) {
            $groupReceivers = $this->groupRepo->findByNames($receivers['groups']);
        }

        if (count($receivers['workspaces']) > 0) {
            $workspaceReceivers = $this->workspaceRepo->findByCodes($receivers['workspaces']);
        }

        if ($setAsSent && $message->getSender()) {
            $userMessage = new UserMessage();
            $userMessage->setIsSent(true);
            $userMessage->setUser($message->getSender());
            $userMessage->setMessage($message);

            $this->om->persist($userMessage);
        }

        $mailNotifiedUsers = [];

        //get every users which are going to be notified
        foreach ($groupReceivers as $groupReceiver) {
            $users = $this->userRepo->findByGroup($groupReceiver);

            foreach ($users as $user) {
                $userReceivers[] = $user;
            }
        }

        //workspaces are going to be notified
        foreach ($workspaceReceivers as $workspaceReceiver) {
            $users = $this->userRepo->findByWorkspaceWithUsersFromGroup($workspaceReceiver);

            foreach ($users as $user) {
                $userReceivers[] = $user;
            }
        }

        $ids = [];

        $filteredUsers = array_filter($userReceivers, function (User $user) use (&$ids) {
            if (!in_array($user->getId(), $ids)) {
                $ids[] = $user->getId();

                return true;
            }

            return false;
        });

        foreach ($filteredUsers as $filteredUser) {
            $userMessage = new UserMessage();
            $userMessage->setUser($filteredUser);
            $userMessage->setMessage($message);
            $this->om->persist($userMessage);

            if ($filteredUser->isMailNotified()) {
                $mailNotifiedUsers[] = $filteredUser;
            }
        }

        if ($sendMail) {
            $replyToMail = !empty($message->getSender()) ? $message->getSender()->getEmail() : null;

            $this->mailManager->send(
                $message->getObject(),
                $message->getContent(),
                $mailNotifiedUsers,
                $message->getSender(),
                [],
                false,
                $replyToMail
            );
        }

        $this->om->flush();

        return $message;
    }

    public function sendMessageToAbstractRoleSubject(
        AbstractRoleSubject $subject,
        $content,
        $object,
        $sender = null,
        $withMail = true
    ) {
        $users = [];

        if ($subject instanceof User) {
            $users[] = $subject;
        }

        if ($subject instanceof Group) {
            foreach ($subject->getUsers() as $user) {
                $users[] = $user;
            }
        }

        $message = $this->create($content, $object, $users, $sender);
        $this->send($message, true, $withMail);
    }

    public function remove(Message $message)
    {
        $this->om->remove($message);
        $this->om->flush();
    }
}
