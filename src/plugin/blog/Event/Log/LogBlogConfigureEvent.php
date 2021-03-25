<?php

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\BlogBundle\Entity\BlogOptions;

class LogBlogConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_blog-configure';

    /**
     * @param array $changeSet
     */
    public function __construct(BlogOptions $blogOptions, $changeSet)
    {
        $blog = $blogOptions->getBlog();

        $details = [
            'blog' => [
                'blog' => $blog->getId(),
                'options' => json_encode($blogOptions),
                'changeSet' => $changeSet,
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
