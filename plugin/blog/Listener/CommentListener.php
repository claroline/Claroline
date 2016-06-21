<?php

/*
* This file is part of the Claroline Connect package.
*
* (c) Claroline Consortium <consortium@claroline.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Icap\BlogBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\BlogBundle\Entity\Comment;
use Icap\NotificationBundle\Entity\UserPickerContent;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

/**
 * @DI\Service("icap.blog_bundle.entity_listener.comment")
 * @DI\Tag("doctrine.entity_listener")
 */
class CommentListener
{
    /** @var  \Icap\NotificationBundle\Manager\NotificationManager */
    private $notificationManager;

    /**
     * @DI\InjectParams({
     * "notificationManager" = @DI\Inject("icap.notification.manager"),
     * })
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Comment $comment, LifecycleEventArgs $event)
    {
        $userPicker = $comment->getUserPicker();
        $post = $comment->getPost();
        $blog = $post->getBlog();
        if (
            $post->isPublished() &&
            $comment->isPublished() &&
            $userPicker !== null &&
            count($userPicker->getUserIds()) > 0 &&
            $blog->getResourceNode() !== null
        ) {
            $details = array(
                'post' => array(
                    'blog' => $blog->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                ),
                'comment' => array(
                    'id' => $comment->getId(),
                    'content' => $comment->getMessage(),
                    'published' => $comment->isPublished(),
                    'author' => $comment->getAuthor()->getFirstName().' '.$post->getAuthor()->getLastName(),
                    'authorId' => $comment->getAuthor()->getId(),
                ),
                'resource' => array(
                    'id' => $blog->getId(),
                    'name' => $blog->getResourceNode()->getName(),
                    'type' => $blog->getResourceNode()->getResourceType()->getName(),
                ),
            );
            $notification = $this->notificationManager->createNotification(
                'resource-icap_blog-comment-user_tagged',
                'blog',
                $blog->getResourceNode()->getId(),
                $details
            );
            $this->notificationManager->notifyUsers($notification, $userPicker->getUserIds());
        }
    }

    public function prePersist(Comment $comment, LifecycleEventArgs $event)
    {
        if ($comment->getMessage() != null) {
            $userPicker = new UserPickerContent($comment->getMessage());
            $comment->setUserPicker($userPicker);
            $comment->setMessage($userPicker->getFinalText());
        }
    }

    public function preUpdate(Comment $comment, LifecycleEventArgs $event)
    {
        $this->prePersist($comment, $event);
    }

    public function postUpdate(Comment $comment, LifecycleEventArgs $event)
    {
        $this->postPersist($comment, $event);
    }
}
