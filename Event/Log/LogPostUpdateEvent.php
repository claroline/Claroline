<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Post;

class LogPostUpdateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-post_update';

    /**
     * @param Post  $post
     * @param array $changeSet
     */
    public function __construct(Post $post, $changeSet)
    {
        $blog = $post->getBlog();

        $details = array(
            'post' => array(
                'blog'      => $blog->getId(),
                'title'     => $post->getTitle(),
                'slug'      => $post->getSlug(),
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
