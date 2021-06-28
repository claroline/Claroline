<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class LogSectionMoveEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-section_move';

    /**
     * @param array $changeSet
     */
    public function __construct(Wiki $wiki, Section $section, $changeSet)
    {
        $details = [
            'section' => [
                'wiki' => $wiki->getId(),
                'id' => $section->getId(),
                'title' => $section->getActiveContribution()->getTitle(),
                'text' => $section->getActiveContribution()->getText(),
                'visible' => $section->getVisible(),
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
