<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Calendar;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Claroline\CoreBundle\Entity\Organization\Organization;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__time_slot")
 */
class TimeSlot
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column(name="start", type="datetime")
     * @Assert\NotBlank()
     * @Groups({"api"})
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="datetime")
     * @Assert\NotBlank()
     * @Groups({"api"})
     */
    protected $end;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     cascade={"persist"},
     *     inversedBy="timeSlots"
     * )
     * @ORM\JoinColumn(name="organization_id", onDelete="CASCADE", nullable=false)
     */
    protected $organization;

    public function getId()
    {
        return $this->id;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getOrganization()
    {
        return $this->organization;
    }
}
