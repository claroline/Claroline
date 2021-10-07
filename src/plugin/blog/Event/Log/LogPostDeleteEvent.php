<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\Post;

class LogPostDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-post_delete';

    public function __construct(Post $post)
    {
        $blog = $post->getBlog();

        $details = [
            'post' => [
                'blog' => $blog->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'author' => $post->getCreator()->getFirstName().' '.$post->getCreator()->getLastName(),
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
