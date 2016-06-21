<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\ForumBundle\Entity\Category;

class CreateCategoryEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_forum-create_category';

    /**
     * @param \Claroline\ForumBundle\Entity\Category $category
     */
    public function __construct(Category $category)
    {
        $details = array(
            'category' => array(
                'id' => $category->getId(),
            ),
            'forum' => array(
                'id' => $category->getForum()->getId(),
            ),
        );

        parent::__construct($category->getForum()->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
