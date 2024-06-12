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

use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\CoreBundle\Entity\Location;
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
     */
    private ?string $type = null;

    /**
     * The FQCN of the AbstractPlanned implementation.
     * It allows us to retrieve the event from the core (used for PlannedObjectVoter).
     *
     * @ORM\Column(name="event_class")
     */
    private ?string $class = null;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $startDate = null;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $endDate = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $color = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $locationUrl = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     */
    private ?Location $location = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate = null): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate = null): void
    {
        $this->endDate = $endDate;
    }

    public function isTerminated(): bool
    {
        $now = new \DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color = null): void
    {
        $this->color = $color;
    }

    public function getLocationUrl(): ?string
    {
        return $this->locationUrl;
    }

    public function setLocationUrl(string $locationUrl = null): void
    {
        $this->locationUrl = $locationUrl;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location = null): void
    {
        $this->location = $location;
    }
}
