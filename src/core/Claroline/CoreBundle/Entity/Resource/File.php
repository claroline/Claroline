<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(type="string") 
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
    
    public function setMime($mime)
    {
        $this->mime= $mime;
    }
    
    //this function will create a mime type for the resource !
    public function createAndSetMime($extension)
    {
        //~
        switch($extension)
        {
            //videos
            case "mp4":
                $this->mime = 'video/mp4';break;
            case "mov":
                $this->mime = 'video/mov';break;
            case "flv":
                $this->mime = 'video/flv';break;
            //audio
            case "ogg":
                $this->mime = 'audio/ogg';break;
            //application
            case "zip":
                $this->mime = 'application/zip';break;
            //images
            case "pnj":
                $this->mime = 'image/pnj';break;
            case "bmp":
                $this->mime = 'image/bmp';break;
            case "jpg":
                $this->mime = 'image/jpg';break;
            case "jpeg":
                $this->mime = 'image/jpeg';break;
            //text
            case "txt":
                $this->mime = 'text/txt';break;
            default:
                $this->mime = null; 
        }
    }
}