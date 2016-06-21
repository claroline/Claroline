<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;

class LogWikiConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-configure';

    /**
     * @param Wiki    $wiki
     * @param Section $section
     * @param array   $changeSet
     */
    public function __construct(Wiki $wiki, $changeSet)
    {
        $details = array(
            'wiki' => array(
                'wiki' => $wiki->getId(),
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
