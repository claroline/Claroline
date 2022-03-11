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

class LogResourceDeleteEvent extends LogGenericEvent
{
    const ACTION = 'resource-delete';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $node)
    {
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
                'owner' => $node->getCreator() ? [
                    'lastName' => $node->getCreator()->getLastName(),
                    'firstName' => $node->getCreator()->getFirstName(),
                ] : [
                    'lastName' => 'unknown',
                    'firstName' => 'unknown',
                ],
            ],
            null,
            null,
            null,
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
