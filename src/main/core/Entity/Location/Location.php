<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Location;

use Claroline\AppBundle\Entity\Address;
use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__location")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Location implements IdentifiableInterface
{
    use Id;
    use Uuid;
    use Description;
    use Thumbnail;
    use Poster;
    use Address;

    const TYPE_DEPARTMENT = 1;
    const TYPE_USER = 2;
    const TYPE_TRAINING = 3;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $type = self::TYPE_DEPARTMENT;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    private $longitude;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $phone;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="locations"
     * )
     * @ORM\JoinTable(name="claro_user_location")
     *
     * @var ArrayCollection
     */
    private $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     mappedBy="locations"
     * )
     *
     * @var Organization[]|ArrayCollection
     */
    private $organizations;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="locations"
     * )
     * @ORM\JoinTable(name="claro_group_location")
     *
     * @var ArrayCollection
     */
    private $groups;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->groups = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->organizations = new ArrayCollection();
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setType(int $type = null)
    {
        $this->type = $type;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setPhone(string $phone = null)
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setLatitude(float $latitude = null)
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLongitude(float $longitude = null)
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function setOrganizations(Organization $organizations)
    {
        $this->organizations = $organizations;
    }

    /**
     * @return Organization[]|ArrayCollection
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    public function addUser(User $user)
    {
        $user->getLocations()->add($this);
    }

    public function removeUser(User $user)
    {
        $user->getLocations()->removeElement($this);
    }

    public function addOrganization(Organization $organization)
    {
        $organization->addLocation($this);
    }

    public function removeOrganization(Organization $organization)
    {
        $organization->removeLocation($this);
    }

    public function addGroup(Group $group)
    {
        $group->getLocations()->add($this);
    }

    public function removeGroup(Group $group)
    {
        $group->getLocations()->removeElement($this);
    }
}
