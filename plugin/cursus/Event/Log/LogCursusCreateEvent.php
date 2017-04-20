<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CursusBundle\Entity\Cursus;

class LogCursusCreateEvent extends LogGenericEvent
{
    const ACTION = 'cursusbundle-cursus-create';

    public function __construct(Cursus $cursus)
    {
        $details = [];
        $details['id'] = $cursus->getId();
        $details['title'] = $cursus->getTitle();
        $details['code'] = $cursus->getCode();
        $details['blocking'] = $cursus->isBlocking();
        $details['details'] = $cursus->getDetails();
        $details['root'] = $cursus->getRoot();
        $details['lvl'] = $cursus->getLvl();
        $details['lft'] = $cursus->getLft();
        $details['rgt'] = $cursus->getRgt();
        $parent = $cursus->getParent();
        $course = $cursus->getCourse();
        $workspace = $cursus->getWorkspace();
        $organizations = $cursus->getOrganizations();

        if (!is_null($parent)) {
            $details['parentId'] = $parent->getId();
            $details['parentTitle'] = $parent->getTitle();
            $details['parentCode'] = $parent->getCode();
        }
        if (!is_null($course)) {
            $details['courseId'] = $course->getId();
            $details['courseTitle'] = $course->getTitle();
            $details['courseCode'] = $course->getCode();
        }
        if (!is_null($workspace)) {
            $details['workspaceId'] = $workspace->getId();
            $details['workspaceName'] = $workspace->getName();
            $details['workspaceCode'] = $workspace->getCode();
            $details['workspaceGuid'] = $workspace->getGuid();
        }
        foreach ($organizations as $organization) {
            $details['organizations'][] = [
                'id' => $organization->getId(),
                'name' => $organization->getName(),
                'default' => $organization->isDefault(),
            ];
        }

        parent::__construct(self::ACTION, $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_ADMIN];
    }
}
