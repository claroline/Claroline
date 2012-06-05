<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_text")
 */
class Text extends AbstractResource
{
    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $text;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $version;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Text", inversedBy="children")
     * @ORM\JoinColumn(name="old_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\Text", mappedBy="parent")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $children;
    
    public function getText()
    {
        return $this->text;
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    public function addVersion()
    {
        $this->version++;
    }       
    
    public function setParent(Text $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }    
}