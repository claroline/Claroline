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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_booking_material_booking")
 */
class MaterialBooking
{
    use Description;
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Location\Material")
     * @ORM\JoinColumn(name="material_id", nullable=false, onDelete="CASCADE")
     *
     * @var Material
     */
    private $material;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     *
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     *
     * @var \DateTimeInterface
     */
    private $endDate;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getMaterial(): Material
    {
        return $this->material;
    }

    public function setMaterial(Material $material)
    {
        $this->material = $material;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate)
    {
        $this->endDate = $endDate;
    }
}
