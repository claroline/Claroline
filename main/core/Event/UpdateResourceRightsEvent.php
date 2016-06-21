<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;

class UpdateResourceRightsEvent extends Event
{
    private $node;
    private $rights;

    public function __construct(ResourceNode $node, ResourceRights $rights)
    {
        $this->node = $node;
        $this->rights = $rights;
    }

    public function getNode(ResourceNode $node)
    {
        return $this->node;
    }

    public function getResourceRights(ResourceRights $rights)
    {
        return $this->rights;
    }
}
