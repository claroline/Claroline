<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Planning;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Location\Room;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_planned_object")
 */
class PlannedObject
{
    use Id;
    use CreatedAt;
    use Creator;
    use Description;
    use UpdatedAt;
    use Uuid;
    use Name;
    use Poster;
    use Thumbnail;

    /**
     * @ORM\Column(name="event_type")
     *
     * @var string
     */
    private $type;

    /**
     * The FQCN of the AbstractPlanned implementation.
     * It allows us to retrieve the event from the core (used for PlannedObjectVoter).
     *
     * @ORM\Column(name="event_class")
     *
     * @var string
     */
    private $class;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    private $endDate;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $color;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $locationUrl;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Location\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     *
     * @var Location
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Location\Room")
     * @ORM\JoinColumn(name="room_id", nullable=true, onDelete="SET NULL")
     *
     * @var Room
     */
    private $room;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class)
    {
        $this->class = $class;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function isTerminated()
    {
        $now = new \DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color = null)
    {
        $this->color = $color;
    }

    public function getLocationUrl(): ?string
    {
        return $this->locationUrl;
    }

    public function setLocationUrl(string $locationUrl = null)
    {
        $this->locationUrl = $locationUrl;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location = null)
    {
        $this->location = $location;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room = null)
    {
        $this->room = $room;
    }
}
