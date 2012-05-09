<?php

namespace Claroline\HTMLPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_html_element")
 */
class HTMLElement extends AbstractResource
{
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $archive;
    
    /**
     * @ORM\Column(type="string", length=255, name="hash_name") 
     */
    private $hashName;
    
    /**
     * @ORM\Column(type="string", length=255, name="index_page") 
     */
    private $indexPage;
    
    public function setArchive($archive)
    {
        $this->archive=$archive;
    }
    
    public function getArchive()
    {
        return $this->archive;
    }
            
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }
    
    public function getHashName()
    {
        return $this->hashName;
    }
    
    public function setIndex($indexPage)
    {
        $this->indexPage=$indexPage;
    }
    
    public function getIndex()
    {
        return $this->indexPage;
    }
            
          
}