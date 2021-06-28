<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Location;

use Claroline\AppBundle\Entity\IdentifiableInterface;
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
 * @ORM\Table(name="claro_location_room")
 */
class Room implements IdentifiableInterface
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Location\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     *
     * @var Location
     */
    private $location;

    /**
     * @ORM\Column(name="capacity", nullable=false, type="integer")
     * @Assert\NotBlank()
     * @Assert\PositiveOrZero()
     */
    private $capacity;

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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location = null): void
    {
        $this->location = $location;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity)
    {
        $this->capacity = $capacity;
    }
}
