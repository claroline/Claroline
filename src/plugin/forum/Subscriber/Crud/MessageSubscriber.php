<?php

namespace Claroline\ForumBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Manager\ForumManager;
use Claroline\ForumBundle\Messenger\NotifyUsersOnMessageCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly ForumManager $forumManager,
        private readonly MessageBusInterface $messageBus
    ) {
        $this->authorization = $authorization;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Message::class) => 'preCreate',
            Crud::getEventName('create', 'post', Message::class) => 'postCreate',
        ];
    }

    /**
     * Manage moderation on message create.
     */
    public function preCreate(CreateEvent $event): void
    {
        /** @var Message $message */
        $message = $event->getObject();
        $forum = $message->getSubject()->getForum();

        // create user if not here
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
    }

    /**
     * Send notifications after creation.
     */
    public function postCreate(CreateEvent $event): void
    {
        /** @var Message $message */
        $message = $event->getObject();

        $this->messageBus->dispatch(new NotifyUsersOnMessageCreated($message->getId()));
    }
}
