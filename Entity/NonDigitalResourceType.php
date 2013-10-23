<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NonDigitalResourceType
 *
 * @ORM\Table("innova_nonDigitalResourceType")
 * @ORM\Entity
 */
class NonDigitalResourceType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @ORM\OneToMany(targetEntity="NonDigitalResource", mappedBy="nonDigitalResourceType")
    */
    protected $nonDigitalResources;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string   $name
     * @return NonDigitalResourceType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nonDigitalResources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add nonDigitalResources
     *
     * @param \Innova\PathBundle\Entity\NonDigitalResource $nonDigitalResources
     * @return NonDigitalResourceType
     */
    public function addNonDigitalResource(\Innova\PathBundle\Entity\NonDigitalResource $nonDigitalResources)
    {
        $this->nonDigitalResources[] = $nonDigitalResources;

        return $this;
    }

    /**
     * Remove nonDigitalResources
     *
     * @param \Innova\PathBundle\Entity\NonDigitalResource $nonDigitalResources
     */
    public function removeNonDigitalResource(\Innova\PathBundle\Entity\NonDigitalResource $nonDigitalResources)
    {
        $this->nonDigitalResources->removeElement($nonDigitalResources);
    }

    /**
     * Get nonDigitalResources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNonDigitalResources()
    {
        return $this->nonDigitalResources;
    }
}
