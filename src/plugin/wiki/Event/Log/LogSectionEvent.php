<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class LogSectionEvent extends AbstractLogResourceEvent
{
    public function __construct($action, Wiki $wiki, Section $section)
    {
        $node = $wiki->getResourceNode();

        $details = [
            'section' => [
                'wiki' => $wiki->getId(),
                'id' => $section->getId(),
                'title' => $section->getActiveContribution()->getTitle(),
                'text' => $section->getActiveContribution()->getText(),
                'author' => $section->getAuthor()->getFirstName().' '.$section->getAuthor()->getLastName(),
            ],
        ];

        parent::__construct($node, $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
