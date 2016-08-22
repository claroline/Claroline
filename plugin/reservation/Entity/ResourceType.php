<?php

namespace FormaLibre\ReservationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Groups({"api_reservation", "api_cursus"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotNull()
     * @Assert\Length(min="2", max="50")
     * @Groups({"api_reservation", "api_cursus"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="FormaLibre\ReservationBundle\Entity\Resource", mappedBy="resourceType")
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
