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

class LogResourceMoveEvent extends LogGenericEvent
{
    const ACTION = 'resource-move';

    /**
     * Constructor.
     * $resource is the moved object
     * while $source is the original object's parent (before move action).
     */
    public function __construct(ResourceNode $node, ResourceNode $source)
    {
        $owner = [];
        if (!empty($node->getCreator())) {
            $owner = [
                'lastName' => $node->getCreator()->getLastName(),
                'firstName' => $node->getCreator()->getFirstName(),
            ];
        }

        parent::__construct(
            self::ACTION,
            [
                'resource' => [
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay(),
                ],
                'workspace' => [
                    'name' => $node->getWorkspace()->getName(),
                ],
                'owner' => $owner,
                'source' => [
                    'resource' => [
                        'id' => $source->getId(),
                        'name' => $source->getName(),
                        'path' => $source->getPathForDisplay(),
                    ],
                    'workspace' => [
                        'id' => $source->getWorkspace()->getId(),
                        'name' => $source->getWorkspace()->getName(),
                    ],
                ],
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
