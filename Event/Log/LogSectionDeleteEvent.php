<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;

class LogSectionDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-section_delete';

    /**
* @param Wiki $wiki
* @param Section $section
*/
    public function __construct(Wiki $wiki, Section $section)
    {
        $details = array(
            'section' => array(
                'wiki' => $wiki->getId(),
                'id' => $section->getId(),
                'title' => $section->getTitle(),
                'text' => $section->getText(),
                'creator' => $section->getCreator()->getFirstName() . ' ' . $section->getCreator()->getLastName()
            )
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