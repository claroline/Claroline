<?php

namespace Claroline\ForumBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Manager\ForumManager;
use Claroline\ForumBundle\Messenger\NotifyUsersOnMessageCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubjectSubscriber implements EventSubscriberInterface
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly MessageBusInterface $messageBus,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly ForumManager $forumManager,
        private readonly FileManager $fileManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Subject::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Subject::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Subject::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Subject::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $forum = $subject->getForum();

        // create user if not here
        if ($subject->getCreator()) {
            $user = $this->om->getRepository(UserValidation::class)->findOneBy([
                'user' => $subject->getCreator(),
                'forum' => $forum,
            ]);

            if (!$user) {
                $user = new UserValidation();
                $user->setForum($forum);
                $user->setUser($subject->getCreator());
            }
        }

        $moderation = Forum::VALIDATE_NONE;
        if (!$this->checkPermission('EDIT', $forum->getResourceNode())) {
            $moderation = $forum->getValidationMode();

            if (Forum::VALIDATE_PRIOR_ONCE === $moderation && $user->getAccess()) {
                // user has already posted an accepted message
                $moderation = Forum::VALIDATE_NONE;
            }
        }

        $subject->setModerated($moderation);

        $messages = $subject->getMessages();
        $first = $messages && isset($messages[0]) ? $messages[0] : null;
        if ($first) {
            $first->setModerated($moderation);

            $this->om->persist($first);
        }
    }

    /**
     * Send notifications after creation.
     */
    public function postCreate(CreateEvent $event): void
    {
        /** @var Subject $subject */
        $subject = $event->getObject();

        if ($subject->getPoster()) {
            $this->fileManager->linkFile(Subject::class, $subject->getUuid(), $subject->getPoster()->getUrl());
        }

        $message = $subject->getFirstMessage();
        if ($message) {
            // hacky : when we are in a flushSuite (e.g. copy), the messenger will fail because the message does not exist
            $this->om->forceFlush();
            $this->messageBus->dispatch(new NotifyUsersOnMessageCreated($message->getId()));
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Subject::class,
            $subject->getUuid(),
            !empty($subject->getPoster()) ? $subject->getPoster()->getUrl() : null,
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Subject $subject */
        $subject = $event->getObject();

        if ($subject->getPoster()) {
            $this->fileManager->unlinkFile(Subject::class, $subject->getUuid(), $subject->getPoster()->getUrl());
        }
    }
}
