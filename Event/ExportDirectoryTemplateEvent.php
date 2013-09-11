<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class ExportDirectoryTemplateEvent extends Event implements DataConveyorEventInterface
{
    private $node;
    private $config;
    private $isPopulated = false;

    public function __construct(ResourceNode $node)
    {
        $this->node = $node;
        $this->files = array();
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
