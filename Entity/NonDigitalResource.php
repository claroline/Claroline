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
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
    * @ORM\ManyToOne(targetEntity="NonDigitalResourceType", inversedBy="nonDigitalResources")
    */
    protected $NonDigitalResourceType;

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


}
