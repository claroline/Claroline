<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;

class LogPostCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-post_create';

    public function __construct(Blog $blog, Post $post)
    {
        $details = array(
            'post' => array(
                'blog'  => $blog->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            )
        );

        parent::__construct($blog->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
