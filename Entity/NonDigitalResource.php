<?php

namespace Innova\PathBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * NonDigitalResource
 *
 * @ORM\Table("innova_nonDigitalResource")
 * @ORM\Entity
 */
class NonDigitalResource extends AbstractResource
{
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
    * @ORM\ManyToOne(targetEntity="NonDigitalResourceType", inversedBy="nonDigitalResources")
    */
    protected $nonDigitalResourceType;

    /**
     * Set description
     *
     * @param  string             $description
     * @return NonDigitalResource
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set nonDigitalResourceType
     *
     * @param \Innova\PathBundle\Entity\NonDigitalResourceType $nonDigitalResourceType
     * @return NonDigitalResource
     */
    public function setNonDigitalResourceType(\Innova\PathBundle\Entity\NonDigitalResourceType $nonDigitalResourceType = null)
    {
        $this->nonDigitalResourceType = $nonDigitalResourceType;

        return $this;
    }

    /**
     * Get nonDigitalResourceType
     *
     * @return \Innova\PathBundle\Entity\NonDigitalResourceType 
     */
    public function getNonDigitalResourceType()
    {
        return $this->nonDigitalResourceType;
    }
}
