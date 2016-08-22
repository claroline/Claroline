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
    const TYPE_DEPARTMENT = 1;
    const TYPE_USER = 2;
    const TYPE_TRAINING = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_location", "api_organization_list", "api_organization_tree", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_organization_list", "api_organization_tree", "api_user_min"})
     */
    protected $name;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_user_min"})
     */
    protected $street;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_user_min"})
     */
    protected $streetNumber;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_location", "api_user_min"})
     */
    protected $boxNumber;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_user_min"})
     */
    protected $pc;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_user_min"})
     */
    protected $town;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_location", "api_user_min"})
     */
    protected $country;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api_location", "api_user_min"})
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api_location", "api_user_min"})
     */
    protected $longitude;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_location", "api_user_min"})
     */
    protected $phone;

    /**
     * @var User[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_user_location")
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     mappedBy="locations"
     * )
     */
    protected $organizations;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_user_min"})
     */
    protected $type = self::TYPE_DEPARTMENT;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->organizations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setPc($pc)
    {
        $this->pc = $pc;
    }

    public function getPc()
    {
        return $this->pc;
    }

    public function setTown($town)
    {
        $this->town = $town;
    }

    public function getTown()
    {
        return $this->town;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setOrganizations(Organization $organizations)
    {
        $this->organizations = $organizations;
    }

    public function getOrganizations()
    {
        return $this->organizations;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    }

    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    public function setBoxNumber($boxNumber)
    {
        $this->boxNumber = $boxNumber;
    }

    public function getBoxNumber()
    {
        return $this->boxNumber;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
