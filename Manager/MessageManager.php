<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Pager\PagerFactory;

/**
 * @DI\Service("claroline.manager.message_manager")
 */
class MessageManager
{
    const MESSAGE_READ = 'Read';
    const MESSAGE_SENT = 'Sent';
    const MESSAGE_REMOVED = 'Removed';
    const MESSAGE_UNREMOVED = 'Unremoved';

    private $om;
    private $pagerFactory;
    private $groupRepo;
    private $userRepo;
    private $messageRepo;
    private $userMessageRepo;
    private $mailManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"   = @DI\Inject("claroline.pager.pager_factory"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        MailManager $mailManager
    )
    {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->messageRepo = $om->getRepository('ClarolineCoreBundle:Message');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->userMessageRepo = $om->getRepository('ClarolineCoreBundle:UserMessage');
        $this->pagerFactory = $pagerFactory;
        $this->mailManager = $mailManager;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User    $sender
     * @param \Claroline\CoreBundle\Entity\Message $message
     * @param \Claroline\CoreBundle\Entity\Message $parent
     *                                                      @param boolean setAsSent
     *
     * @return \Claroline\CoreBundle\Entity\Message
     */

    public function send(User $sender, Message $message, $parent = null, $setAsSent = true)
    {
        if (substr($receiversString = $message->getTo(), -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, '', -1);
        }

        $receiversNames = explode(';', $receiversString);
        $usernames = array();
        $groupNames = array();
        $workspaceCodes = array();
        $userReceivers = array();
        $groupReceivers = array();
        $workspaceReceivers = array();

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

        $message->setSender($sender);

        if (null !== $parent) {
            $message->setParent($parent);
        }

        $this->om->persist($message);

        if ($setAsSent) {
            $userMessage = $this->om->factory('Claroline\CoreBundle\Entity\UserMessage');
            $userMessage->setIsSent(true);
            $userMessage->setUser($sender);
            $userMessage->setMessage($message);
            $this->om->persist($userMessage);
        }

        $mailNotifiedUsers = array();

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
            $userMessage = $this->om->factory('Claroline\CoreBundle\Entity\UserMessage');
            $userMessage->setUser($filteredUser);
            $userMessage->setMessage($message);
            $this->om->persist($userMessage);

            if ($user->isMailNotified()) {
                $mailNotifiedUsers[] = $filteredUser;
            }
        }

        $this->mailManager->send($message->getObject(), $message->getContent(), $mailNotifiedUsers );
        $this->om->flush();

        return $message;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $receiver
     * @param string                            $search
     * @param integer                           $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getReceivedMessages(User $receiver, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findReceived($receiver, false):
            $this->userMessageRepo->findReceivedByObjectOrSender($receiver, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $sender
     * @param string                            $search
     * @param integer                           $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getSentMessages(User $sender, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findSent($sender, false) :
            $this->userMessageRepo->findSentByObject($sender, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string                            $search
     * @param integer                           $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getRemovedMessages(User $user, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findRemoved($user, false):
            $this->userMessageRepo->findRemovedByObjectOrSender($user, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Message $message
     *
     * @return \Claroline\CoreBundle\Entity\Message[]
     */
    public function getConversation(Message $message)
    {
        return $this->messageRepo->findAncestors($message);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return integer
     */
    public function getNbUnreadMessages(User $user)
    {
        return $this->messageRepo->countUnread($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User      $user
     * @param \Claroline\CoreBundle\Entity\Message[] $messages
     */
    public function markAsRead(User $user, array $messages)
    {
        $userMessages = $this->userMessageRepo->findByMessages($user, $messages);

        $this->markMessages($userMessages, self::MESSAGE_READ);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Message[] $userMessages
     */
    public function markAsRemoved(array $userMessages)
    {
        $this->markMessages($userMessages, self::MESSAGE_REMOVED);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Message[] $userMessages
     */
    public function markAsUnremoved(array $userMessages)
    {
        $this->markMessages($userMessages, self::MESSAGE_UNREMOVED);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Message[] $userMessages
     */
    public function remove(array $userMessages)
    {
        foreach ($userMessages as $userMessage) {
            $this->om->remove($userMessage);
        }

        $this->om->flush();
    }

    /**
     * Generates a query string containing the list of user ids in a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     *
     * @return string
     */
    public function generateGroupQueryString(Group $group)
    {
        $users = $this->userRepo->findByGroup($group);
        $queryString = '?';

        for ($i = 0, $count = count($users); $i < $count; $i++) {
            if ($i > 0) {
                $queryString .= "&";
            }

            $queryString .= "ids[]={$users[$i]->getId()}";
        }

        return $queryString;
    }

    /**
     * Generates a query string containing the list of user ids in a group.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return string
     */
    public function generateWorkspaceQueryString(AbstractWorkspace $workspace)
    {
        $users = $this->userRepo->getByWorkspaceWithUsersFromGroup($workspace);
        $queryString = '?';

        for ($i = 0, $count = count($users); $i < $count; $i++) {
            if ($i > 0) {
                $queryString .= "&";
            }

            $queryString .= "ids[]={$users[$i]->getId()}";
        }

        return $queryString;
    }

    /**
     * Generates a string containing the usernames from a list of users.
     *
     * @param \Claroline\CoreBundle\Entity\User[] $receivers
     *
     * @return string
     */
    public function generateStringTo(array $receivers)
    {
        $usernames = array();

        foreach ($receivers as $receiver) {
            $usernames[] = $receiver->getUsername();
        }

        return implode(';', $usernames);
    }

    public function getUserMessagesBy(array $array)
    {
        return $this->userMessageRepo->findBy($array);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Message[] $userMessages
     * @param string                                 $flag
     */
    private function markMessages(array $userMessages, $flag)
    {
        $method = 'markAs' . $flag;

        foreach ($userMessages as $userMessage) {
            $userMessage->$method();
            $this->om->persist($userMessage);
        }

        $this->om->flush();
    }
}
