<?php

namespace Claroline\CoreBundle\Event\Planning;

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Symfony\Contracts\EventDispatcher\Event;

class UnplanObjectEvent extends Event
{
    /** @var AbstractPlanned */
    private $planned;
    /** @var IdentifiableInterface */
    private $object;

    public function __construct(
        AbstractPlanned $planned,
        IdentifiableInterface $object
    ) {
        $this->planned = $planned;
        $this->object = $object;
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
