<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceExportEvent extends LogGenericEvent
{
    const ACTION = 'resource-export';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $node)
    {
        parent::__construct(
            self::ACTION,
            array(
                'resource' => array(
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay(),
                ),
                'workspace' => array(
                    'name' => $node->getWorkspace()->getName(),
                ),
                'owner' => array(
                    'lastName' => $node->getCreator()->getLastName(),
                    'firstName' => $node->getCreator()->getFirstName(),
                ),
            ),
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $node->getCreator()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
    }
}
