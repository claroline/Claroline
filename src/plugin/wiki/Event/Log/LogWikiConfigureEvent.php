<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;

class LogWikiConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-configure';

    /**
     * @param Section $section
     * @param array   $changeSet
     */
    public function __construct(Wiki $wiki, $changeSet)
    {
        $details = [
            'wiki' => [
                'wiki' => $wiki->getId(),
                'changeSet' => $changeSet,
            ],
        ];

        parent::__construct($wiki->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
