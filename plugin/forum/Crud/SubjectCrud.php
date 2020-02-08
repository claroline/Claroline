<?php

namespace Claroline\ForumBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubjectCrud
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var MessageManager */
    private $messageManager;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /**
     * ForumSerializer constructor.
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
     * @param CreateEvent $event
     *
     * @return Subject
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $forum = $subject->getForum();

        //create user if not here
        $user = $this->om->getRepository(UserValidation::class)->findOneBy([
            'user' => $subject->getCreator(),
            'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($subject->getCreator());
        }

        $messages = $subject->getMessages();
        $first = $messages && isset($messages[0]) ? $messages[0] : null;

        if (!$this->checkPermission('EDIT', $forum->getResourceNode())) {
            if (Forum::VALIDATE_PRIOR_ALL === $forum->getValidationMode()) {
                $subject->setModerated(Forum::VALIDATE_PRIOR_ALL);
                if ($first) {
                    $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
                }
            }

            if (Forum::VALIDATE_PRIOR_ONCE === $forum->getValidationMode()) {
                $subject->setModerated($user->getAccess() ? Forum::VALIDATE_NONE : Forum::VALIDATE_PRIOR_ONCE);
                if ($first) {
                    $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
                }
            }
        } else {
            $subject->setModerated(Forum::VALIDATE_NONE);
            if ($first) {
                $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
            }
        }

        if ($first) {
            $this->om->persist($first);
        }

        return $subject;
    }

    /**
     * Send notifications after creation.
     *
     * @param CreateEvent $event
     *
     * @return Subject
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $forum = $subject->getForum();

        $message = $subject->getFirstMessage();
        if ($message) {
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
        }

        return $subject;
    }
}
