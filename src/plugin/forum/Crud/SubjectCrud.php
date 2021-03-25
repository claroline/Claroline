<?php

namespace Claroline\ForumBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubjectCrud
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ForumManager */
    private $forumManager;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /**
     * ForumSerializer constructor.
     */
    public function __construct(
        ObjectManager $om,
        ForumManager $forumManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->forumManager = $forumManager;
        $this->authorization = $authorization;
    }

    /**
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
     * @return Subject
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();

        $message = $subject->getFirstMessage();
        if ($message) {
            $this->forumManager->notifyMessage($message);
        }

        return $subject;
    }
}
