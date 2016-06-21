<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;

class LogPostReadEvent extends AbstractLogResourceEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource-icap_blog-post_read';

    /**
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $blog = $post->getBlog();

        $details = array(
            'post' => array(
                'blog' => $blog->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
            ),
        );

        parent::__construct($blog->getResourceNode(), $details);
    }

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->resource->getId();
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
