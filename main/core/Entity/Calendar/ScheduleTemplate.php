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
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Calendar\ScheduleTemplateRepository")
 * @ORM\Table(name="claro__schedule_template")
 */
class ScheduleTemplate
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column(name="start", type="datetime")
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Groups({"api"})
     */
    protected $day;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     * @Groups({"api"})
     */
    protected $startHour;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     * @Groups({"api"})
     */
    protected $endHour;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;
    }

    public function getStartHour()
    {
        return $this->startHour;
    }
}
