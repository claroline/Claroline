<?php

namespace Claroline\ForumBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.forum_message")
 * @DI\Tag("claroline.crud")
 */
class MessageCrud
{
    use PermissionCheckerTrait;

    /**
     * ForumSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "messageManager" = @DI\Inject("claroline.manager.message_manager")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        MessageManager $messageManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->messageManager = $messageManager;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_forumbundle_entity_message")
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
        $user = $this->om->getRepository('ClarolineForumBundle:Validation\User')->findOneBy([
          'user' => $message->getCreator(),
          'forum' => $forum,
        ]);

        if (!$user) {
            $user = new User();
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
     * @DI\Observe("crud_post_create_object_claroline_forumbundle_entity_message")
     *
     * @param CreateEvent $event
     *
     * @return ResourceNode
     */
    public function postCreate(CreateEvent $event)
    {
        $message = $event->getObject();
        $forum = $message->getSubject()->getForum();

        $usersValidate = $this->om->getRepository('ClarolineForumBundle:Validation\User')
          ->findBy(['forum' => $forum, 'notified' => true]);

        $toSend = $this->messageManager->create(
          $message->getContent(),
          $message->getSubject()->getTitle(),
          array_map(function ($userValidate) {
              return $userValidate->getUser();
          }, $usersValidate)
        );
        $this->messageManager->send($toSend);

        return $message;
    }

    public function getSubject(Message $message)
    {
        if (!$message->getSubject()) {
            $parent = $message->getParent();

            return $this->getSubject($parent);
        }

        return $message->getSubject();
    }
}
