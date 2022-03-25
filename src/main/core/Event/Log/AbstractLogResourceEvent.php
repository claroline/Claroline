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

abstract class AbstractLogResourceEvent extends LogGenericEvent
{
    const ACTION = '';

    /**
     * Constructor.
     *
     * LogResourceEvent is used by plugins for creating custom events when
     * action occured on a resource, or child resource (e.g. post in forum,
     * comment in blog, event in calendar etc.)
     *
     * Possible changes over a resource's child are: creation, delete, update, published, unpublished, etc.
     *
     * "$details" is an array that contains all necessary info to describe indirect resource modification.
     *
     * For example when a comment is published to a blog resource the details could be:
     *
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
        $creator = $node->getCreator();

        $commonDetails = [
            'resource' => [
                'name' => $node->getName(),
                'path' => $node->getPathForDisplay(),
                'uuid' => $node->getUuid(),
                'id' => $node->getId(),
                'slug' => $node->getSlug(),
            ],
            'workspace' => [
                'name' => $node->getWorkspace()->getName(),
                'slug' => $node->getWorkspace()->getSlug(),
            ],
            'owner' => $creator ? [
                'lastName' => $creator->getLastName(),
                'firstName' => $creator->getFirstName(),
            ] : [],
        ];

        $detailsData = array_merge($commonDetails, $details);

        parent::__construct(
            $this->getAction(),
            $detailsData,
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $creator,
            null
        );
    }

    public function getAction()
    {
        return $this->action ? $this->action : static::ACTION;
    }
}
