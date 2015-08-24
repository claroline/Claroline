<?php

namespace FormaLibre\ReservationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="formalibre_reservation_resource")
 * @ORM\Entity()
 * @UniqueEntity({"name", "resourceType"})
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
     * @Assert\Length(min="2", max="50")
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(name="max_time_reservation", type="string", length=8, nullable=true)
     */
    private $maxTimeReservation;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\ResourceType", inversedBy="resources")
     * @ORM\JoinColumn(name="resource_type", nullable=false, onDelete="CASCADE")
     */
    private $resourceType;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="localisation", type="string", length=255, nullable=true)
     * @Assert\Length(min="2", max="255")
     */
    private $localisation;

    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     * @Assert\Range(min=1)
     */
    private $quantity;

    public function getId()
    {
        return $this->id;
    }

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

    // maxTime must be store like hh:mm:ss
    public function setMaxTimeReservation($maxTime)
    {
        if ($maxTime === null) {
            $maxTime = '00:00:00';
        } elseif (count(explode(':', $maxTime)) === 2) {
            $maxTime = $maxTime . ':00';
        }

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
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getLocalisation()
    {
        return $this->localisation;
    }

    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}