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

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Claroline\CoreBundle\Entity\Organization\Organization;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Calendar\YearRepository")
 * @ORM\Table(name="claro__year")
 */
class Year
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
     * @ORM\Column()
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     * @Groups({"api"})
     */
    protected $openHour;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     * @Groups({"api"})
     */
    protected $closeHour;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     cascade={"persist"},
     *     inversedBy="years"
     * )
     * @ORM\JoinColumn(name="organization_id", onDelete="CASCADE", nullable=false)
     */
    protected $organization;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\Leave",
     *     mappedBy="year",
     *     cascade={"persist"}
     * )
     */
    protected $leaves;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\Period",
     *     mappedBy="year",
     *     cascade={"persist"}
     * )
     */
    protected $periods;

    public function __construct()
    {
        $this->leaves = new ArrayCollection();
        $this->periods = new ArrayCollection();
    }

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

    public function setOpenHour($openHour)
    {
        $this->openHour = $openHour;
    }

    public function getOpenHour()
    {
        return $this->openHour;
    }

    public function setCloseHour($closeHour)
    {
        $this->closeHour = $closeHour;
    }

    public function getCloseHour()
    {
        return $this->closeHour;
    }

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getOrganization()
    {
        return $this->organization;
    }

    public function addLeave(Leave $leave)
    {
        if (!$this->leaves->contains($leave)) {
            $this->leaves->add($leave);
        }
    }

    public function removeLeave(Leave $leave)
    {
        if ($this->leaves->contains($leave)) {
            $this->leaves->removeElement($leave);
        }
    }

    public function setLeaves(ArrayCollection $leaves)
    {
        $this->leaves = $leaves;
    }

    public function getLeaves()
    {
        return $this->leaves;
    }

    public function addPeriod(Period $period)
    {
        if (!$this->periods->contains($period)) {
            $this->periods->add($period);
        }
    }

    public function removePeriod(Period $period)
    {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
        }
    }

    public function setPeriod(ArrayCollection $periods)
    {
        $this->periods = $periods;
    }

    public function getPeriod()
    {
        return $this->periods;
    }
}
