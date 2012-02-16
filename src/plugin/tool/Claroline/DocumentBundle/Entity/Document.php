<?php

namespace Claroline\DocumentBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_document")
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

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
     * @ORM\Column(type="string", length=32, name="hash_name")
     * @Assert\NotBlank
     */
    private $hashName;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\DocumentBundle\Entity\Directory", inversedBy="documents")
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id")
     */
    private $directory;
    
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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
    
    public function getDirectory()
    {
        return $this->directory;
    }
    
    public function setDirectory(Directory $directory)
    {
       $this->directory=$directory;
    }
    
    public function getHashName()
    {
        return $this->hashName;
    }
    
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }
    
    
}