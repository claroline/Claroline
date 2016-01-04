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
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__organization")
 * @DoctrineAssert\UniqueEntity("name")
 * @Gedmo\Tree(type="nested")
 */
class Organization
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"api"})
     */
    protected $position;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api"})    
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api"})    
     */
    protected $email;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Location",
     *     mappedBy="organization",
     *     cascade={"persist"}
     * )
     * @Groups({"api"})
     */
    protected $locations;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Groups({"api"})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Organization", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @Groups({"api"})
     */
    private $children;

    /**
     * @var User[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_user_organization")
     */
    protected $users;
    
    public function __construct()
    {
        $this->locations   = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->users       = new ArrayCollection();
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

    public function getDepartments()
    {
        return $this->departments;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}
