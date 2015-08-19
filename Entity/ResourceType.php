<?php

namespace FormaLibre\ReservationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * @ORM\Table(name="formalibre_reservation_resource_type")
 * @ORM\Entity()
 */
class ResourceType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length="255")
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="localisation", type="string", length="255")
     */
    private $localisation;

    /**
     * @ORM\OneToMany(targetEntity="ReservationBundle\Entity\ResourceType", mappedBy="resource_type")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $resources;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

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

    public function getResources()
    {
        return $this->resources;
    }

    public function addResource(Resource $resource)
    {
        $this->resources[] = $resource;
        $resource->setResourceType($this);

        return $this;
    }

    public function removeResource(Resource $resource)
    {
        $this->resources->removeElement($resource);

        return $this;
    }
}