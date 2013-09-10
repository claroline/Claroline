<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

abstract class AbstractLogResourceEvent extends LogGenericEvent
{
    /**
     * Constructor.
     * LogResourceEvent is used by plugins for creating custom events when action occured on a resource, or child resource
     * (e.g. post in forum, comment in blog, event in calendar etc.)
     * Possible changes over a resource's child are: creation, delete, update, published, unpublished, etc.
     *
     * details is an array that contains all necessary info to describe indirect resource modification.
     * For example when a comment is published to a blog resource the details could be:
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
     * Please respect lowerCamelCase naming convention for property names in details
     */
    public function __construct(ResourceNode $node, $details)
    {
        $commenDetails = array(
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
            )
        );

        $detailsDatas = array_merge($commenDetails, $details);

        parent::__construct(
            static::ACTION,
            $detailsDatas,
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $node->getCreator(),
            null
        );
    }
}
