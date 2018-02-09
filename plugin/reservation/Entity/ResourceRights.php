<?php

namespace FormaLibre\ReservationBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="formalibre_reservation_resource_rights",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="reservation_unique_resource_rights",
 *             columns={"role_id", "resource_id"}
 *         )
 *     }
 * )
 * @ORM\Entity()
 * @UniqueEntity({"role", "resource"})
 */
class ResourceRights
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=0)
     * @Assert\NotNull()
     */
    private $mask = 0;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\Resource", inversedBy="resourceRights")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @Assert\NotNull()
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @Assert\NotNull()
     */
    private $role;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mask.
     *
     * @param int $mask
     *
     * @return ResourceRights
     */
    public function setMask($mask)
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * Get mask.
     *
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * Set resource.
     *
     * @param \FormaLibre\ReservationBundle\Entity\Resource $resource
     *
     * @return ResourceRights
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource.
     *
     * @return \FormaLibre\ReservationBundle\Entity\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set role.
     *
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @return ResourceRights
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
