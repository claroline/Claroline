<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Gives an entity the ability to be ordered in a collection.
 */
trait Order
{
    /**
     * Order of the element.
     */
    #[ORM\Column(name: 'entity_order', type: Types::INTEGER)]
    protected int $order = 0;

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
