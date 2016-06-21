<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;

class LogSectionUpdateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-section_update';

    /**
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     */
    public function __construct(Wiki $wiki, Section $section, $changeSet)
    {
        $details = array(
            'section' => array(
                'wiki' => $wiki->getId(),
                'id' => $section->getId(),
                'title' => $section->getActiveContribution()->getTitle(),
                'text' => $section->getActiveContribution()->getText(),
                'visible' => $section->getVisible(),
                'changeSet' => $changeSet,
            ),
        );

        parent::__construct($wiki->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
