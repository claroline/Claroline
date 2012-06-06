<?php
namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_text")
 */
class Text extends AbstractResource
{

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $version;
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\TextContent", mappedBy="text", cascade={"persist"})
     */
    protected $contents;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\TextContent", cascade={"persist"})
     * @ORM\JoinColumn(name="current_text_id", referencedColumnName="id")
     */
    protected $text;
    
    public function __construct()
    {
        $this->version = 1;
        $this->contents = new ArrayCollection();
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function setVersion($version)
    {
        $this->version = $version;
    }   
    
    public function getContents()
    {
        return $this->contents;
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function addContent($content)
    {
        $this->contents->add($content);
    }
    
    public function removeUser($content)
    {
        $this->contents->removeElement($content);
    }
}