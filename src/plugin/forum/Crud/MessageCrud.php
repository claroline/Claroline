<?php

namespace Claroline\ForumBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Manager\ForumManager;
use Claroline\ForumBundle\Messenger\ForumNotification;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MessageCrud
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ForumManager */
    private $forumManager;

    /** @var MessageBusInterface */
    private $messageBus;

    /**
     * MessageCrud constructor.
     */
    public function __construct(
        ObjectManager $om,
        ForumManager $forumManager,
        AuthorizationCheckerInterface $authorization,
        MessageBusInterface $messageBus
    ) {
        $this->om = $om;
        $this->forumManager = $forumManager;
        $this->authorization = $authorization;
        $this->messageBus = $messageBus;
    }

    /**
     * Manage moderation on message create.
     *
     * @return Message
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();
        $forum = $message->getSubject()->getForum();

        //create user if not here
        $user = $this->om->getRepository(UserValidation::class)->findOneBy([
            'user' => $message->getCreator(),
            'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($message->getCreator());
        }
        if (!$this->checkPermission('EDIT', $forum->getResourceNode())) {
            if (Forum::VALIDATE_PRIOR_ALL === $forum->getValidationMode()) {
                $message->setModerated(Forum::VALIDATE_PRIOR_ALL);
            }

            if (Forum::VALIDATE_PRIOR_ONCE === $forum->getValidationMode()) {
                $message->setModerated($user->getAccess() ? Forum::VALIDATE_NONE : Forum::VALIDATE_PRIOR_ONCE);
            }
        } else {
            $message->setModerated(Forum::VALIDATE_NONE);
        }

        return $message;
    }

    /**
     * Send notifications after creation.
     *
     * @return Message
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();

        $this->messageBus->dispatch(new ForumNotification($message->getUuid()));

        return $message;
    }
}
