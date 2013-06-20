<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\MessageRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Message as Msg;
use Claroline\CoreBundle\Writer\MessageWriter as Writer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.message_manager")
 */
class MessageManager
{
    /** @var UserRepository */
    private $userRepo;
    /** @var MessageRepository */
    private $messageRepo;
    /** @var Writer */
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" = @DI\Inject("user_repository"),
     *     "messageRepo" = @DI\Inject("message_repository"),
     *     "writer" = @DI\Inject("claroline.writer.message_writer")
     * })
     */
    public function __construct(UserRepository $userRepo, MessageRepository $messageRepo, Writer $writer)
    {
        $this->userRepo = $userRepo;
        $this->messageRepo = $messageRepo;
        $this->writer = $writer;
    }

    public function create(User $sender, $receiversString, $content, $object, Msg $parent = null)
    {
        if (substr($receiversString, -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, "", -1);
        }

        $usernames = explode(';', $receiversString);
        $receivers = $this->userRepo->findByUsernames($usernames);

        return $this->writer->create($sender, $receiversString, $receivers, $content, $object, $parent);
    }

    public function markAsRead(User $user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsRead($userMessage);
        }
    }

    public function markAsRemoved(User $user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsRemoved($userMessage);
        }
    }

    public function markAsUnremoved(User $user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsUnremoved($userMessage);
        }
    }

    public function remove(User $user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->remove($userMessage);
        }
    }

    public function generateGroupQueryString(Group $group)
    {
        $users = $this->userRepo->findByGroup($group);
        $urlParameters = '?';

        for ($i = 0, $count = count($users); $i < $count; $i++) {
            if ($i > 0) {
                $urlParameters .= "&";
            }

            $urlParameters .= "ids[]={$users[$i]->getId()}";
        }

        return $urlParameters;
    }

    public function generateStringTo(array $userIds)
    {
        $usersString = '';
        $users = $this->userRepo->findByIds($userIds);

        foreach ($users as $user) {
            $usersString .= "{$user->getUsername()};";
        }

        return $usersString;
    }
}