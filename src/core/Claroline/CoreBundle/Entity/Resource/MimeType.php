<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_mime_type")
 */
class MimeType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;
    
   /**
    * @ORM\Column(type="string")
    */
    private $name;
    
   /**
    * @ORM\Column(type="string")
    */
    private $type;
    
   /**
    * @ORM\Column(type="string")
    */
    private $extension;
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\File", mappedBy="mime", cascade={"persist"})
     */
    protected $files;
    
    public function getName()
    {
        return $this->name;
    }
    
    //set Types and Extension
    public function setName($name)
    {
        $this->name = $name;
        $array = explode('/', $name);
        $this->setType($array[0]);
        $this->setExtension($array[1]);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getType()
    {
       return $this->type;
    }
    
    public function setType($type)
    {
         $this->type = $type;
    }
    
    public function getExtension()
    {
        return $this->extension;
    }
    
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }
    
    public function getFiles()
    {
        return $this->files;
    }
}