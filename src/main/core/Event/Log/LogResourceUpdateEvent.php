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
        if ($changeSet != null && count($changeSet) == 1 && array_key_exists('name', $changeSet)) {
            $action = self::ACTION_RENAME;
        }

        parent::__construct(
            $action,
            array(
                'resource' => array(
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay(),
                    'changeSet' => $changeSet,
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
