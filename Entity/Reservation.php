<?php

namespace FormaLibre\ReservationBundle\Entity;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * @ORM\Table(name="formalibre_reservation")
 * @ORM\Entity()
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
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\Resource")
     * @ORM\JoinColumn(name="resource", nullable=false)
     */
    private $resource;

    /**
     * @ORM\Column(name="last_update", type="integer")
     */
    private $lastUpdate;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\AgendaBundle\Entity\Event")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $event;

    public function getId()
    {
        return $this->id;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($hours, $minutes)
    {
        $this->duration = $hours * 60 + $minutes;
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

    public function getLastUpdate()
    {
        $lastUpdate = Date('d-m-Y H:i', $this->lastUpdate);
        return new \DateTime($lastUpdate);
    }

    public function setLastUpdate($lastUpdate)
    {
        if ($lastUpdate instanceof \Datetime) {
            $this->lastUpdate = $lastUpdate->getTimestamp();
        } elseif (is_int($lastUpdate)) {
            $this->lastUpdate = $lastUpdate;
        } else {
            throw new \Exception('Not an integer nor a date.');
        }
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
}