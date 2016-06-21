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
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Calendar\LeaveRepository")
 * @ORM\Table(name="claro__leave")
 */
class Leave
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank()
     * @Groups({"api"})
     */
    protected $date;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\Year",
     *     cascade={"persist"},
     *     inversedBy="leaves"
     * )
     * @ORM\JoinColumn(name="year_id", onDelete="CASCADE", nullable=false)
     */
    protected $year;

    public function getId()
    {
        return $this->id;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setYear(Year $year)
    {
        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }
}
