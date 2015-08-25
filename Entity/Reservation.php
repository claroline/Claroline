<?php

namespace FormaLibre\ReservationBundle\Entity;

use Claroline\AgendaBundle\Entity\Event;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FormaLibre\ReservationBundle\Validator\Constraints as Validator;

/**
 * @ORM\Table(name="formalibre_reservation")
 * @ORM\Entity(repositoryClass="FormaLibre\ReservationBundle\Repository\ReservationRepository")
 * @Validator\DateRange()
 * @Validator\Duration()
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank()
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\Resource")
     * @ORM\JoinColumn(name="resource", nullable=false)
     * @Assert\NotNull()
     */
    private $resource;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\AgendaBundle\Entity\Event", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", name="event_id")
     */
    private $event;

    private $start;

    private $end;

    public function getId()
    {
        return $this->id;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }
}