<?php

namespace Claroline\CoreBundle\Event\Planning;

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Symfony\Contracts\EventDispatcher\Event;

class UnplanObjectEvent extends Event
{
    public function __construct(
        private readonly AbstractPlanned $planned,
        private readonly IdentifiableInterface $object
    ) {
    }

    public function getPlanned(): AbstractPlanned
    {
        return $this->planned;
    }

    public function getObject(): IdentifiableInterface
    {
        return $this->object;
    }
}
