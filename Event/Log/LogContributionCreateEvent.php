<?php

namespace Icap\WikiBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Contribution;

class LogContributionCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_wiki-contribution_create';

/**
 * @param Wiki $wiki
 * @param Section $section
 * @param Contribution $contribution
*/
    public function __construct(Wiki $wiki, Section $section, Contribution $contribution)
    {
        $details = array(
            'contribution' => array(
                'wiki' => $wiki->getId(),
                'section' => $section->getId(),
                'id' => $contribution->getId(),
                'title' => $contribution->getTitle(),
                'text' => $contribution->getText(),
                'contributor' => $contribution->getContributor()->getFirstName() . ' ' . $contribution->getContributor()->getLastName()
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