<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\Mime;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_file")
 */
class File extends AbstractResource
{
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * @ORM\Column(type="datetime", name="date_upload")
     * @Gedmo\Timestampable(on="update")
     */
    private $dateUpload;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $size;
    
    /**
     * @ORM\Column(type="string", length=36, name="hash_name") 
     */
    private $hashName;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Mime", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(name="mime_id", referencedColumnName="id")
     */
    protected $mime;

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setDateUpload($dateUpload)
    {
        $this->dateUpload = $dateUpload;
    }

    public function getDateUpload()
    {
        return $this->dateUpload;
    }

    public function getFormatSize()
    {
        if ($this->size < 1024)
        {
            return $this->size . ' B';
        }
        elseif ($this->size < 1048576)
        {
            return round($this->size / 1024, 2) . ' KB';
        }
        elseif ($this->size < 1073741824)
        {
            return round($this->size / 1048576, 2) . ' MB';
        }
        elseif ($this->size < 1099511627776)
        {
            return round($this->size / 1073741824, 2) . ' GB';
        }
        else
        {
            return round($this->size / 1099511627776, 2) . ' TB';
        }
    }
    
    public function getHashName()
    {
        return $this->hashName;
    }
    
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }
    
    public function getMime()
    {
        return $this->mime;
    }
    
    public function setMime(Mime $mime)
    {
        $this->mime= $mime;
    }    
}