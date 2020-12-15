<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gives an entity the ability to be ordered in a collection.
 */
trait Order
{
    /**
     * Order of the element.
     *
     * @ORM\Column(name="entity_order", type="integer")
     *
     * @var int
     */
    private $order = 0;

    public function setOrder(int $order)
    {
        $this->order = $order;
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}
