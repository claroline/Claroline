<?php

namespace VendorX\ResourceXBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="vx_resource_y")
 */
/*Abstract*/ class ResourceY extends AbstractResource
{
    /**
     * @ORM\Column(type="string", name="some_field", length=255)
     */
    private $someField;
    
    public function getSomeField() 
    {
        return $this->someField;
    }

    public function setSomeField($value) 
    {
        $this->someField = $value;
    }
}