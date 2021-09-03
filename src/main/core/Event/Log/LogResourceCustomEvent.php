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

class LogResourceCustomEvent extends LogGenericEvent
{
    const ACTION = 'resource-custom_action';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $node, $action)
    {
        $owner = [];
        if (!empty($node->getCreator())) {
            $owner = [
                'lastName' => $node->getCreator()->getLastName(),
                'firstName' => $node->getCreator()->getFirstName(),
            ];
        }

        parent::__construct(
            self::ACTION.'_'.$action,
            [
                'resource' => [
                    'name' => $node->getName(),
                    'path' => $node->getPathForCreationLog(),
                ],
                'workspace' => [
                    'name' => $node->getWorkspace()->getName(),
                ],
                'owner' => $owner,
                'action' => $action,
            ],
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
