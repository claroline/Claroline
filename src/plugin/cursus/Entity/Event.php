<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\CoreBundle\Entity\Organization\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\EventRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event")
 */
class Event
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
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Session",
     *     inversedBy="events"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var Session
     */
    protected $session;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     *
     * @var Location
     */
    protected $location;

    /**
     * @ORM\Column(name="location_extra", type="text", nullable=true)
     */
    protected $locationExtra;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     */
    protected $maxUsers;

    /**
     * @ORM\Column(name="registration_type", type="integer", nullable=false, options={"default" = 0})
     */
    protected $registrationType = Session::REGISTRATION_AUTO;

    /**
     * SessionEvent constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function isTerminated()
    {
        $now = new \DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(Location $location = null)
    {
        $this->location = $location;
    }

    public function getLocationExtra()
    {
        return $this->locationExtra;
    }

    public function setLocationExtra($locationExtra)
    {
        $this->locationExtra = $locationExtra;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getRegistrationType()
    {
        return $this->registrationType;
    }

    public function setRegistrationType($registrationType)
    {
        $this->registrationType = $registrationType;
    }
}
