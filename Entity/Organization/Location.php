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

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__location")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Location
{
    const TYPE_DEPARTMENT = 1;
    const TYPE_USER       = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $name;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $street;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $streetNumber;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api"})
     */
    protected $boxNumber;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $pc;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $town;

    /**
     * @ORM\Column()
     * @Groups({"api"})
     */
    protected $country;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api"})
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"api"})
     */
    protected $longitude;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api"})
     */
    protected $phone;


    /**
     * @var Role[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_user_location")
     */
    protected $users;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="locations"
     * )
     * @ORM\JoinColumn(name="organization_id", onDelete="CASCADE", nullable=true)
     */
    protected $organization;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type = self::TYPE_DEPARTMENT;

    public function __construct()
    {
        $this->users  = new ArrayCollection();
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

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getOrganization()
    {
        return $this->organization;
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
