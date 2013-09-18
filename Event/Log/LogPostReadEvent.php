<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;

class LogPostReadEvent extends AbstractLogResourceEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource-icap_blog-post_read';

    public function __construct(Blog $blog, Post $post)
    {
        $details = array(
            'post' => array(
                'blog'  => $post->getBlog()->getId(),
                'title' => $post->getTitle(),
                'slug'  => $post->getSlug()
            )
        );

        parent::__construct($blog->getResourceNode(), $details);
    }

    public function getLogSignature()
    {
        return self::ACTION.'_' . $this->resource->getId();
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
