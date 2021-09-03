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

class LogResourceCopyEvent extends LogGenericEvent
{
    const ACTION = 'resource-copy';

    /**
     * Constructor.
     * $resource is the final copy
     * while $source is the original object.
     */
    public function __construct(ResourceNode $resource, ResourceNode $source)
    {
        $owner = [];
        if (!empty($resource->getCreator())) {
            $owner = [
                'lastName' => $resource->getCreator()->getLastName(),
                'firstName' => $resource->getCreator()->getFirstName(),
            ];
        }

        parent::__construct(
            self::ACTION,
            [
                'resource' => [
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay(),
                ],
                'workspace' => [
                    'name' => $resource->getWorkspace()->getName(),
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
            $resource,
            null,
            $resource->getWorkspace(),
            $resource->getCreator()
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
