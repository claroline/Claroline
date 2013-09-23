<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Post;

class LogPostDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-post_delete';

    /**
     * @param Blog $blog
     * @param Post $post
     */
    public function __construct(Blog $blog, Post $post)
    {
        $details = array(
            'post' => array(
                'blog'   => $blog->getId(),
                'title'  => $post->getTitle(),
                'slug'   => $post->getSlug(),
                'author' => $post->getAuthor()->getFirstName() . ' ' . $post->getAuthor()->getLastName()
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
