<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;
use Claroline\CoreBundle\Repository\MessageRepository;
use Claroline\CoreBundle\Repository\UserMessageRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Database\Writer;
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

    private $messageRepo;
    private $userMessageRepo;
    private $userRepo;
    private $writer;
    private $pagerFactory;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "messageRepo"        = @DI\Inject("message_repository"),
     *     "userMessageRepo"    = @DI\Inject("claroline.repository.user_message_repository"),
     *     "userRepo"           = @DI\Inject("user_repository"),
     *     "writer"             = @DI\Inject("claroline.database.writer"),
     *     "pagerFactory"       = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(
        UserMessageRepository $userMessageRepo,
        MessageRepository $messageRepo,
        UserRepository $userRepo,
        Writer $writer,
        PagerFactory $pagerFactory
    )
    {
        $this->userRepo = $userRepo;
        $this->messageRepo = $messageRepo;
        $this->writer = $writer;
        $this->writer = $writer;
        $this->userMessageRepo = $userMessageRepo;
        $this->pagerFactory = $pagerFactory;
    }

    public function send(User $sender, Message $message, $parent = null)
    {
        if (substr($receiversString = $message->getTo(), -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, '', -1);
        }

        $usernames = explode(';', $receiversString);
        $receivers = $this->userRepo->findByUsernames($usernames); // throw ex if missing
        $message->setSender($sender);

        // replace receiver string by to !!!!
        $message->setReceiverString($receiversString);

        if (null !== $parent) {
            $message->setParent($parent);
        }

        $this->writer->suspendFlush();
        $this->writer->create($message);

        $userMessage = new UserMessage();
        $userMessage->setIsSent(true);
        $userMessage->setUser($sender);
        $userMessage->setMessage($message);
        $this->writer->create($userMessage);

        foreach ($receivers as $receiver) {
            $userMessage = new UserMessage();
            $userMessage->setUser($receiver);
            $userMessage->setMessage($message);
            $this->writer->create($userMessage);
        }

        $this->writer->forceFlush();
    }

    // return pager
    public function getReceivedMessages(User $receiver, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findReceived($receiver, false):
            $this->userMessageRepo->findReceivedByObjectOrSender($receiver, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    // return pager
    public function getSentMessages(User $sender, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findSent($sender, false) :
            $this->userMessageRepo->findSentByObjectOrSender($sender, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    // return pager
    public function getRemovedMessages(User $user, $search = '', $page = 1)
    {
        $query = $search === '' ?
            $this->userMessageRepo->findRemoved($user, false):
            $this->userMessageRepo->findRemovedByUserAndObjectAndUsername($user, $search, false);

        return $this->pagerFactory->createPager($query, $page);
    }

    public function getConversation(Message $message)
    {
        return $this->messageRepo->findAncestors($message);
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
        $this->writer->suspendFlush();

        foreach ($userMessages as $userMessage) {
            $this->writer->remove($userMessage);
        }

        $this->writer->forceFlush();
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
        $this->writer->suspendFlush();

        foreach ($userMessages as $userMessage) {
            $userMessage->$method($userMessage);
            $this->writer->update($userMessage);
        }

        $this->writer->forceFlush();
    }
}