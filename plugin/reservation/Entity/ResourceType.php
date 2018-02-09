<?php

namespace FormaLibre\ReservationBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(
 *     name="formalibre_reservation_resource_type",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="reservation_unique_resource_type",
 *             columns={"name"}
 *         )
 *     }
 * )
 * @ORM\Entity()
 */
class ResourceType
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_reservation", "api_cursus"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name")
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
        $this->refreshUuid();

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
