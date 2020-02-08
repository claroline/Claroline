<?php

namespace Claroline\ForumBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MessageCrud
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var MessageManager */
    private $messageManager;

    /**
     * MessageCrud constructor.
     *
     * @param ObjectManager                 $om
     * @param MessageManager                $messageManager
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        ObjectManager $om,
        MessageManager $messageManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->messageManager = $messageManager;
        $this->authorization = $authorization;
    }

    /**
     * Manage moderation on message create.
     *
     * @param CreateEvent $event
     *
     * @return ResourceNode
     */
    public function preCreate(CreateEvent $event)
    {
        $message = $event->getObject();
        $forum = $this->getSubject($message)->getForum();

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
     * @param CreateEvent $event
     *
     * @return Message
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();

        $subject = $this->getSubject($message);
        $forum = $subject->getForum();

        /** @var UserValidation[] $usersValidate */
        $usersValidate = $this->om
            ->getRepository(UserValidation::class)
            ->findBy(['forum' => $forum, 'notified' => true]);

        $toSend = $this->messageManager->create(
            $message->getContent(),
            $subject->getTitle(),
            array_map(function (UserValidation $userValidate) {
                return $userValidate->getUser();
            }, $usersValidate)
        );

        $this->messageManager->send($toSend);

        return $message;
    }

    private function getSubject(Message $message)
    {
        if (!$message->getSubject()) {
            $parent = $message->getParent();

            return $this->getSubject($parent);
        }

        return $message->getSubject();
    }
}
