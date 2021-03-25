<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;

class LogCommentDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-comment_delete';

    public function __construct(Post $post, Comment $comment)
    {
        $author = $comment->getAuthor();
        $blog = $post->getBlog();

        if (null === $author) {
            $author = 'Anonyme';
        } else {
            $author = $comment->getAuthor()->getFirstName().' '.$comment->getAuthor()->getLastName();
        }

        $details = [
            'post' => [
                'blog' => $blog->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
            ],
            'comment' => [
                'id' => $comment->getId(),
                'author' => $author,
                'content' => $comment->getMessage(),
            ],
        ];

        parent::__construct($blog->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
