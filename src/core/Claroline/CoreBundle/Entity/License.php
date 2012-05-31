<?php
namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="claro_license")
 */
class License 
{
    /**
     * @ORM\Column(name="name", type="string") 
     */
    protected $name;
    
    /**
     * @ORM\Column(name="acronym", type="string") 
     */
    protected $acronym;
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setAcronym($acronym)
    {
        $this->acronym = $acronym;
    }
    
    public function getAcronym()
    {
        return $this->acronym;
    }
    
          
}