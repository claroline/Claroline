<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"file" = "File", "directory" = "Directory"})
 */
abstract class AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", name="name") 
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $name;
        
    public function setId($id)
    {
        $this->id=$id;
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
          
}