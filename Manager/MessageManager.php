<?php

namespace Claroline\CoreBundle\Manager;

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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"   = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory
    )
    {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->messageRepo = $om->getRepository('ClarolineCoreBundle:Message');
        $this->userMessageRepo = $om->getRepository('ClarolineCoreBundle:UserMessage');
        $this->pagerFactory = $pagerFactory;
    }

    public function send(User $sender, Message $message, $parent = null)
    {
        if (substr($receiversString = $message->getTo(), -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, '', -1);
        }

        $receiversNames = explode(';', $receiversString);
        $usernames = array();
        $groupNames = array();
        $userReceivers = array();
        $groupReceivers = array();

        foreach ($receiversNames as $receiverName) {
            if (substr($receiverName, 0, 1) === '{') {
                $groupNames[] = trim($receiverName, '{}');
            } else {
                $usernames[] = $receiverName;
            }
        }

        if (count($usernames) > 0) {
            $userReceivers = $this->userRepo->findByUsernames($usernames);
        }
        if (count($groupNames) > 0) {
            $groupReceivers = $this->groupRepo->findGroupsByNames($groupNames);
        }

        $message->setSender($sender);

        if (null !== $parent) {
            $message->setParent($parent);
        }

        $this->om->persist($message);
        $userMessage = $this->om->factory('Claroline\CoreBundle\Entity\UserMessage');
        $userMessage->setIsSent(true);
        $userMessage->setUser($sender);
        $userMessage->setMessage($message);
        $this->om->persist($userMessage);

        foreach ($userReceivers as $userReceiver) {
            $userMessage = $this->om->factory('Claroline\CoreBundle\Entity\UserMessage');
            $userMessage->setUser($userReceiver);
            $userMessage->setMessage($message);
            $this->om->persist($userMessage);
        }

        foreach ($groupReceivers as $groupReceiver) {
            $users = $this->userRepo->findByGroup($groupReceiver);

            foreach ($users as $user) {
                $userMessage = $this->om->factory('Claroline\CoreBundle\Entity\UserMessage');
                $userMessage->setUser($user);
                $userMessage->setMessage($message);
                $this->om->persist($userMessage);
            }
        }

        $this->om->flush();

        return $message;
    }

    public function getReceivedMessages(User $receiver, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findReceived($receiver, false):
            $this->userMessageRepo->findReceivedByObjectOrSender($receiver, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getSentMessages(User $sender, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findSent($sender, false) :
            $this->userMessageRepo->findSentByObject($sender, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getRemovedMessages(User $user, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findRemoved($user, false):
            $this->userMessageRepo->findRemovedByObjectOrSender($user, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getConversation(Message $message)
    {
        return $this->messageRepo->findAncestors($message);
    }

    public function getNbUnreadMessages(User $user)
    {
        return $this->messageRepo->countUnread($user);
    }

    public function markAsRead(User $user, array $messages)
    {
        $this->markMessages($user, $messages, self::MESSAGE_READ);
    }

    public function markAsRemoved(User $user, array $messages)
    {
        $this->markMessages($user, $messages, self::MESSAGE_REMOVED);
    }

    public function markAsUnremoved(User $user, array $messages)
    {
        $this->markMessages($user, $messages, self::MESSAGE_UNREMOVED);
    }

    public function remove(User $user, array $messages)
    {
        $userMessages = $this->userMessageRepo->findByMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->om->remove($userMessage);
        }

        $this->om->flush();
    }

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

    public function generateStringTo(array $receivers)
    {
        $usernames = array();

        foreach ($receivers as $receiver) {
            $usernames[] = $receiver->getUsername();
        }

        return implode(';', $usernames);
    }

    private function markMessages(User $user, array $messages, $flag)
    {
        $userMessages = $this->userMessageRepo->findByMessages($user, $messages);
        $method = 'markAs' . $flag;

        foreach ($userMessages as $userMessage) {
            $userMessage->$method();
            $this->om->persist($userMessage);
        }

        $this->om->flush();
    }
}
