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

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;

class ExportDirectoryTemplateEvent extends Event implements DataConveyorEventInterface
{
    private $node;
    private $config;
    private $isPopulated = false;

    public function __construct(ResourceNode $node)
    {
        $this->node = $node;
        $this->files = [];
    }

    public function getNode()
    {
        return $this->node;
    }

    public function setConfig(array $config)
    {
        $this->isPopulated = true;
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
