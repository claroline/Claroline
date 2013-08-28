<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourceChildUpdateEvent extends LogGenericEvent
{
    const ACTION = 'resource_child_update';

    const CHILD_ACTION_CREATE = 'child_action_create';
    const CHILD_ACTION_READ   = 'child_action_read';
    const CHILD_ACTION_UPDATE = 'child_action_update';
    const CHILD_ACTION_DELETE = 'child_action_delete';

    /**
     * Constructor.
     * LogResourceChildUpdateEvent is used by plugins when a resource's child changed
     * (e.g. post in forum, comment in blog, event in calendar etc.)
     * Possible changes over a resource's child are: creation, delete, update, published, unpublished, etc.
     *
     * ChildDetails is an array that contains all necessary info to describe indirect resource modification.
     * For example when a comment is published to a blog resource the childDetails could be:
     * array(
     *      'comment' => array(
     *          'text' => 'Very useful post thx',
     *          'owner' => array(
     *              'username' => 'JohnDoe',
     *              'email' => 'john.doe@test.test'
     *          )
     *      )
     * )
     *
     * Please respect Underscore naming convention for property names (all lower case words separated with underscores)
     */
    public function __construct(ResourceNode $node, $childType, $childAction, $childDetails)
    {
        parent::__construct(
            self::ACTION,
            array(
                'resource' => array(
                    'name' => $node->getName(),
                    'path' => $node->getPathForDisplay()
                ),
                'workspace' => array(
                    'name' => $node->getWorkspace()->getName()
                ),
                'owner' => array(
                    'lastName' => $node->getCreator()->getLastName(),
                    'firstName' => $node->getCreator()->getFirstName()
                ),
                'child' => $childDetails
            ),
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $node->getCreator(),
            null,
            $childType,
            $childAction
        );
    }
}
