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
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\Revision", mappedBy="text", cascade={"persist"})
     */
    protected $revisions;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Revision", cascade={"persist"})
     * @ORM\JoinColumn(name="current_text_id", referencedColumnName="id")
     */
    protected $lastRevision;
    
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
    
    public function getRevisions()
    {
        return $this->revisions;
    }
    
    public function setLastRevision($revision)
    {
        $this->lastRevision = $revision;
    }
    
    public function getLastRevision()
    {
        return $this->lastRevision;
    }
    
    public function addRevision($revision)
    {
        $this->revisions->add($revision);
    }
    
    public function removeUser($revision)
    {
        $this->revisions->removeElement($revision);
    }
}