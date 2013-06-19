<?php

namespace Claroline\CoreBundle\Library\Message;

use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\MessageRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.message.manager")
 */
class Manager
{
    private $userRepo;
    private $messageRepo;
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "userRepo" = @DI\Inject("user_repository"),
     *     "messageRepo" = @DI\Inject("message_repository"),
     *     "writer" = @DI\Inject("claroline.message.writer")
     * })
     */
    public function __construct(UserRepository $userRepo, MessageRepository $messageRepo, Writer $writer)
    {
        $this->userRepo = $userRepo;
        $this->messageRepo = $messageRepo;
        $this->writer = $writer;
    }

    public function create(User $sender, $receiversString, $content, $object, Message $parent = null)
    {
        if (substr($receiversString, -1, 1) === ';') {
            $receiversString = substr_replace($receiversString, "", -1);
        }

        $usernames = explode(';', $receiversString);
        $receivers = $this->userRepo->findByUsernames($usernames);

        return $this->writer->create($sender, $receiversString, $receivers, $content, $object, $parent);
    }

    public function markAsRead($user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsRead($userMessage);
        }
    }

    public function markAsRemoved($user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsRemoved($userMessage);
        }
    }

    public function markAsUnremoved($user, array $messages)
    {
        $userMessages = $this->messageRepo->findUserMessages($user, $messages);

        foreach ($userMessages as $userMessage) {
            $this->writer->markAsUnremoved($userMessage);
        }
    }
}