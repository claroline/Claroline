<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;

class LogCommentCreateEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_blog-comment_create';
    protected $post;
    protected $comment;
    protected $blog;
    protected $details;

    /**
     * @param Post    $post
     * @param Comment $comment
     */
    public function __construct(Post $post, Comment $comment)
    {
        $this->blog = $post->getBlog();
        $this->comment = $comment;
        $this->post = $post;

        $this->details = array(
            'post' => array(
                'blog'  => $this->blog->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            ),
            'comment' => array(
                'id'        => $comment->getId(),
                'content'   => $comment->getMessage(),
                'published' => $comment->isPublished(),
                'author'    => $comment->getAuthor()->getFirstName()." ".$post->getAuthor()->getLastName(),
                'authorId'  => $comment->getAuthor()->getId()
            )
        );

        parent::__construct($this->blog->getResourceNode(), $this->details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return boolean
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
        return array();
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
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
        return "blog";
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, array());
        $notificationDetails['resource'] = array(
            'id' => $this->blog->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName()
        );

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
