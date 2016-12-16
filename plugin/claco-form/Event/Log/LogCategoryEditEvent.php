<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Event\Log;

use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogCategoryEditEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-category-edit';

    public function __construct(Category $category)
    {
        $clacoForm = $category->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $managers = $category->getManagers();
        $details = [];
        $details['id'] = $category->getId();
        $details['name'] = $category->getName();
        $details['details'] = $category->getDetails();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        $details['managers'] = [];

        foreach ($managers as $manager) {
            $details['managers'][] = [
                'id' => $manager->getId(),
                'username' => $manager->getUsername(),
                'firstName' => $manager->getFirstName(),
                'lastName' => $manager->getLastName(),
            ];
        }
        parent::__construct(self::ACTION, $details, null, null, $resourceNode);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
