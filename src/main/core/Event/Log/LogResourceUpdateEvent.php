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

class LogResourceUpdateEvent extends LogGenericEvent
{
    const ACTION = 'resource-update';
    const ACTION_RENAME = 'resource-update_rename';

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * ).
     *
     * Please respect lower caml case naming convention for property names
     */
    public function __construct(ResourceNode $node, $changeSet)
    {
        $action = self::ACTION;
        if (!empty($changeSet) && 1 === count($changeSet) && array_key_exists('name', $changeSet)) {
            $action = self::ACTION_RENAME;
        }

        $owner = [];
        if (!empty($node->getCreator())) {
            $owner = [
                'lastName' => $node->getCreator()->getLastName(),
                'firstName' => $node->getCreator()->getFirstName(),
            ];
        }

        parent::__construct(
            $action,
            [
                'resource' => [
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay(),
                    'changeSet' => $changeSet,
                ],
                'workspace' => [
                    'name' => $node->getWorkspace()->getName(),
                ],
                'owner' => $owner,
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
