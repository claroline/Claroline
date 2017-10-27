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

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.message_manager")
 */
class MessageManager
{
    const MESSAGE_READ = 'Read';
    const MESSAGE_SENT = 'Sent';
    const MESSAGE_REMOVED = 'Removed';
    const MESSAGE_UNREMOVED = 'Unremoved';

    private $mailManager;
    private $om;
    private $pagerFactory;
    private $groupRepo;
    private $messageRepo;
    private $userMessageRepo;
    private $userRepo;
    private $workspaceRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "mailManager"  = @DI\Inject("claroline.manager.mail_manager"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        MailManager $mailManager,
        ObjectManager $om,
        PagerFactory $pagerFactory
    ) {
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->messageRepo = $om->getRepository('ClarolineMessageBundle:Message');
        $this->userMessageRepo = $om->getRepository('ClarolineMessageBundle:UserMessage');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
    }

    /**
     * Create a message.
     *
     * @param $content      The message content
     * @param $object       The message object
     * @param User[] $users  The users receiving the message
     * @param null   $sender The user sending the message
     * @param null   $parent The message parent (is it's a discussion)
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
        $stringTo = '';

        foreach ($users as $user) {
            $stringTo .= $user->getUsername().';';
        }

        $message->setTo($stringTo);

        return $message;
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message $message
     * @param bool setAsSent
     *
     * @return \Claroline\MessageBundle\Entity\Message
     */
    public function send(
        Message $message,
        $setAsSent = true,
        $sendMail = true
    ) {
        if (substr($receiversString = $message->getTo(), -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, '', -1);
        }

        $receiversNames = explode(';', $receiversString);
        $usernames = [];
        $groupNames = [];
        $workspaceCodes = [];
        $userReceivers = [];
        $groupReceivers = [];
        $workspaceReceivers = [];

        //split the string of target into different array.
        foreach ($receiversNames as $receiverName) {
            if (substr($receiverName, 0, 1) === '{') {
                $groupNames[] = trim($receiverName, '{}');
            } else {
                if (substr($receiverName, 0, 1) === '[') {
                    $workspaceCodes[] = trim($receiverName, '[]');
                } else {
                    $usernames[] = $receiverName;
                }
            }
        }

        //get the different entities from the freshly created array.
        if (count($usernames) > 0) {
            $userReceivers = $this->userRepo->findByUsernames($usernames);
        }

        if (count($groupNames) > 0) {
            $groupReceivers = $this->groupRepo->findGroupsByNames($groupNames);
        }

        if (count($workspaceCodes) > 0) {
            $workspaceReceivers = $this->workspaceRepo->findWorkspacesByCode($workspaceCodes);
        }

        if (null !== $message->getParent()) {
            $message->setParent($message->getParent());
        }

        $this->om->persist($message);

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

        $filteredUsers = array_filter($userReceivers, function ($user) use (&$ids) {
            if (!in_array($user->getId(), $ids)) {
                $ids[] = $user->getId();

                return true;
            }
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
        $replyToMail = !empty($message->getSender()) ? $message->getSender()->getMail() : null;

        if ($sendMail) {
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

    /**
     * @param \Claroline\CoreBundle\Entity\User $receiver
     * @param string                            $search
     * @param int                               $page
     *
     * @deprecated
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getReceivedMessagesPager(User $receiver, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findReceived($receiver, false) :
            $this->userMessageRepo->findReceivedByObjectOrSender($receiver, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getReceivedMessages(User $receiver, $search = '')
    {
        $query = $search === '' ?
        $this->userMessageRepo->findReceived($receiver, false) :
        $this->userMessageRepo->findReceivedByObjectOrSender($receiver, $search, false);

        return $query->getResult();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $sender
     * @param string                            $search
     * @param int                               $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getSentMessagesPager(User $sender, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findSent($sender, false) :
            $this->userMessageRepo->findSentByObject($sender, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getSentMessages(User $sender, $search = '', $page = 1)
    {
        $query = $search === '' ?
        $this->userMessageRepo->findSent($sender, false) :
        $this->userMessageRepo->findSentByObject($sender, $search, false);

        return $query->getResult();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string                            $search
     * @param int                               $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getRemovedMessagesPager(User $user, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findRemoved($user, false) :
            $this->userMessageRepo->findRemovedByObjectOrSender($user, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getRemovedMessages(User $user, $search = '', $page = 1)
    {
        $query = $search === '' ?
        $this->userMessageRepo->findRemoved($user, false) :
        $this->userMessageRepo->findRemovedByObjectOrSender($user, $search, false);

        return $query->getResult();
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message $message
     * @param \Claroline\MessageBundle\Entity\User    $user
     *
     * @return \Claroline\MessageBundle\Entity\Message[]
     */
    public function getConversation(Message $message, User $user)
    {
        return $this->messageRepo->findAncestors($message, $user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return int
     */
    public function getNbUnreadMessages(User $user)
    {
        return $this->messageRepo->countUnread($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User         $user
     * @param \Claroline\MessageBundle\Entity\Message[] $messages
     */
    public function markAsRead(User $user, array $messages)
    {
        $userMessages = $this->userMessageRepo->findByMessages($user, $messages);

        $this->markMessages($userMessages, self::MESSAGE_READ);
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message[] $userMessages
     */
    public function markAsRemoved(array $userMessages)
    {
        $this->markMessages($userMessages, self::MESSAGE_REMOVED);
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message[] $userMessages
     */
    public function markAsUnremoved(array $userMessages)
    {
        $this->markMessages($userMessages, self::MESSAGE_UNREMOVED);
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message[] $userMessages
     */
    public function remove(array $userMessages)
    {
        foreach ($userMessages as $userMessage) {
            $this->om->remove($userMessage);
        }

        $this->om->flush();
    }

    /**
     * Generates a string containing the usernames from a list of users.
     *
     * @param \Claroline\CoreBundle\Entity\User[]      $receivers
     * @param \Claroline\CoreBundle\Entity\Group[]     $groups
     * @param \Claroline\CoreBundle\Entity\Workspace[] $workspaces
     *
     * @return string
     */
    public function generateStringTo(array $receivers, array $groups, array $workspaces)
    {
        $usernames = [];

        foreach ($receivers as $receiver) {
            $usernames[] = $receiver->getUsername();
        }

        $string = implode(';', $usernames);

        foreach ($groups as $group) {
            $el = '{'.$group->getName().'}';
            $string .= $string === '' ? $el : ';'.$el;
        }

        foreach ($workspaces as $workspace) {
            $el = '['.$workspace->getCode().']';
            $string .= $string === '' ? $el : ';'.$el;
        }

        return $string;
    }

    public function getUserMessagesBy(array $array)
    {
        return $this->userMessageRepo->findBy($array);
    }

    /**
     * @param \Claroline\MessageBundle\Entity\Message[] $userMessages
     * @param string                                    $flag
     */
    private function markMessages(array $userMessages, $flag)
    {
        $method = 'markAs'.$flag;

        foreach ($userMessages as $userMessage) {
            $userMessage->$method();
            $this->om->persist($userMessage);
        }

        $this->om->flush();
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

    public function getOneUserMessageByUserAndMessage(User $user, Message $message)
    {
        return $this->userMessageRepo->findOneByUserAndMessage($user, $message);
    }
}
