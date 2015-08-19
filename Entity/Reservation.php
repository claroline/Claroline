<?php

namespace FormaLibre\ReservationBundle\Entity;

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
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="start", type="integer")
     */
    private $start;

    /**
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationBundle\Entity\Resource")
     * @ORM\JoinColumn(name="resource", nullable=false)
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="last_update", type="integer")
     */
    private $lastUpdate;

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getStart()
    {
        $date = Date('d-m-Y H:i', $this->start);
        return new \DateTime($date);
    }

    public function setStart($start)
    {
        if ($start instanceof \Datetime) {
            $this->start = $start->getTimestamp();
        } elseif (is_int($start)) {
            $this->start = $start;
        } else {
            throw new \Exception('Not an integer nor date.');
        }

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {

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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

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
            throw new \Exception('Not an integer nor date.');
        }
    }
}