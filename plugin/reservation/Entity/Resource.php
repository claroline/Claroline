<?php

namespace FormaLibre\ReservationBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="formalibre_reservation_resource")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Resource
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
     * @ORM\Column(name="max_time_reservation", nullable=true)
     * @Groups({"api_reservation", "api_cursus"})
     * @SerializedName("maxTimeReservation")
     */
    private $maxTimeReservation;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\ResourceType", inversedBy="resources")
     * @ORM\JoinColumn(name="resource_type", nullable=false, onDelete="CASCADE")
     * @Groups({"api_reservation", "api_cursus"})
     * @SerializedName("resourceType")
     */
    private $resourceType;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"api_reservation", "api_cursus"})
     */
    private $description;

    /**
     * @ORM\Column(name="localisation", nullable=true)
     * @Groups({"api_reservation", "api_cursus"})
     */
    private $localisation;

    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     * @Groups({"api_reservation", "api_cursus"})
     */
    private $quantity = 1;

    /**
     * @ORM\OneToMany(targetEntity="FormaLibre\ReservationBundle\Entity\ResourceRights", mappedBy="resource")
     * @ORM\JoinColumn(nullable=false)
     */
    private $resourceRights;

    /**
     * @ORM\OneToMany(targetEntity="FormaLibre\ReservationBundle\Entity\Reservation", mappedBy="resource")
     * @ORM\JoinColumn(nullable=true, onDelete="cascade")
     */
    private $reservations;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_reservation", "api_cursus"})
     */
    private $color;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="formalibre_reservation_resource_organizations")
     */
    protected $organizations;

    public function __construct()
    {
        $this->refreshUuid();

        $this->resourceRights = new ArrayCollection();
        $this->organizations = new ArrayCollection();
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

    public function getMaxTimeReservation()
    {
        return $this->maxTimeReservation;
    }

    // maxTime must be store like hh:mm:ss
    public function setMaxTimeReservation($maxTime)
    {
        if (!$maxTime || empty($maxTime)) {
            $maxTime = '00:00:00';
        } elseif (count(explode(':', $maxTime)) === 2) {
            $maxTime = $maxTime.':00';
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

    public function addResourceRight(ResourceRights $resourceRight)
    {
        $this->resourceRights[] = $resourceRight;

        return $this;
    }

    public function removeResourceRight(ResourceRights $resourceRight)
    {
        $this->resourceRights->removeElement($resourceRight);
    }

    public function getResourceRights()
    {
        return $this->resourceRights;
    }

    public function addReservation(Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    public function removeReservation(Reservation $reservation)
    {
        $this->reservations->removeElement($reservation);
    }

    public function getReservations()
    {
        return $this->reservations;
    }

    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getOrganizations()
    {
        return $this->organizations->toArray();
    }

    public function addOrganization(Organization $organization)
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization)
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    public function emptyOrganizations()
    {
        $this->organizations->clear();
    }
}
