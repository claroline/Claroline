<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;

class LogCommentUpdateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-comment_update';

    /**
     * @param Post    $post
     * @param Comment $comment
     */
    public function __construct(Post $post, Comment $comment, $changeSet)
    {
        $blog = $post->getBlog();

        $details = array(
            'post' => array(
                'blog'  => $blog->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            ),
            'comment' => array(
                'id'      => $comment->getId(),
                'content' => $comment->getMessage(),
                'changeSet' => $changeSet
            )
        );

        parent::__construct($blog->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
