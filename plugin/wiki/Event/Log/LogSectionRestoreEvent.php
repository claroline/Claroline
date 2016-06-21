<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;

class LogSectionRestoreEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-section_restore';

    /**
     * @param Wiki    $wiki
     * @param Section $section
     */
    public function __construct(Wiki $wiki, Section $section)
    {
        $details = array(
            'section' => array(
                'wiki' => $wiki->getId(),
                'id' => $section->getId(),
                'title' => $section->getActiveContribution()->getTitle(),
                'text' => $section->getActiveContribution()->getText(),
                'author' => $section->getAuthor()->getFirstName().' '.$section->getAuthor()->getLastName(),
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
