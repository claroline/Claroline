<?php

namespace FormaLibre\ReservationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * @ORM\Table(name="formalibre_reservation_resource")
 * @ORM\Entity()
 */

class Resource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="text")
     */
    private $name;

    /**
     * @ORM\Column(name="max_time_reservation", type="integer")
     */
    private $maxTimeReservation;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\ResourceType", inversedBy="resources")
     * @ORM\JoinColumn(name="resource_type", nullable=false)
     */
    private $resourceType;

    //TODO is_takeable ?

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getMaxTimeReservation()
    {
        return $this->maxTimeReservation;
    }

    public function setMaxTimeReservation($maxTime)
    {
        $this->maxTimeReservation = $maxTime;

        return $this;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function setResourceType(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }
}