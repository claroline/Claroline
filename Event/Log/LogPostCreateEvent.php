<?php

namespace ICAP\BlogBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LogBundle\Event\Log\AbstractLogResourceEvent;
use ICAP\BlogBundle\Entity\Post;

class LogPostCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'post_create';

    public function __construct(ResourceNode $node, Post $post)
    {
        $details = array(
            'post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        parent::__construct($node, $details);
    }
}
