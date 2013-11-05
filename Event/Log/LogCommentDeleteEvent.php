<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;

class LogCommentDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-comment_delete';

    /**
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     */
    public function __construct(Blog $blog, Post $post, Comment $comment)
    {
        $author = $comment->getAuthor();

        if (null === $author) {
            $author = "Anonyme";
        }
        else {
            $author = $comment->getAuthor()->getFirstName() . ' ' . $comment->getAuthor()->getLastName();
        }

        $details = array(
            'post' => array(
                'blog'  => $blog->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            ),
            'comment' => array(
                'author'  => $author,
                'content' => $comment->getMessage()
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
