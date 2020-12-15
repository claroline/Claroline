<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_booking_material")
 */
class Material
{
    use Code;
    use Description;
    use Id;
    use Poster;
    use Thumbnail;
    use Uuid;

    /**
     * @ORM\Column(name="event_name")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(name="capacity", nullable=false, type="integer")
     * @Assert\NotBlank()
     * @Assert\PositiveOrZero()
     */
    private $quantity;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
