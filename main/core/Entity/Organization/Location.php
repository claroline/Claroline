<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Organization;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Organization\LocationRepository")
 * @ORM\Table(name="claro__location")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Location
{
    use UuidTrait;

    const TYPE_DEPARTMENT = 1;
    const TYPE_USER = 2;
    const TYPE_TRAINING = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user_min"})
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_user_min"})
     *
     * @var int
     */
    private $type = self::TYPE_DEPARTMENT;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $street;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $streetNumber;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $boxNumber;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $pc;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $town;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_user_min"})
     *
     * @var string
     */
    private $country;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api_user_min"})
     *
     * @var float
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api_user_min"})
     *
     * @var float
     */
    private $longitude;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_user_min"})
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $pc
     */
    public function setPc($pc)
    {
        $this->pc = $pc;
    }

    /**
     * @return string
     */
    public function getPc()
    {
        return $this->pc;
    }

    /**
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
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

    /**
     * @param Organization $organizations
     */
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

    /**
     * @param string $streetNumber
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @param string $boxNumber
     */
    public function setBoxNumber($boxNumber)
    {
        $this->boxNumber = $boxNumber;
    }

    /**
     * @return string
     */
    public function getBoxNumber()
    {
        return $this->boxNumber;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $user->getLocations()->add($this);
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $user->getLocations()->removeElement($this);
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $group->getLocations()->add($this);
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $group->getLocations()->removeElement($this);
    }
}
