<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;

class LogCommentUpdateEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_blog-comment_update';
    protected $post;
    protected $comment;
    protected $blog;
    protected $details;

    /**
     * @param Post    $post
     * @param Comment $comment
     */
    public function __construct(Post $post, Comment $comment, $changeSet, $translator = null)
    {
        $this->blog = $post->getBlog();
        $this->comment = $comment;
        $this->post = $post;

        $author = $comment->getAuthor()
            ? $comment->getAuthor()->getFirstName().' '.$post->getAuthor()->getLastName()
            : $translator->trans('anonymous', [], 'platform');

        $authorId = $comment->getAuthor()
            ? $comment->getAuthor()->getId()
            : null;

        $this->details = [
            'post' => [
                'blog' => $this->blog->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
            ],
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getMessage(),
                'changeSet' => $changeSet,
                'published' => $comment->isPublished(),
                'author' => $author,
                'authorId' => $authorId,
            ],
        ];

        parent::__construct($this->blog->getResourceNode(), $this->details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        $isPublished = $this->comment->isPublished() && $this->post->isPublished();

        return $isPublished;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return [];
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return [];
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconTypeUrl string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return 'blog';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, []);
        $notificationDetails['resource'] = [
            'id' => $this->blog->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName(),
        ];

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
