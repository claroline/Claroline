<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\File;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_public_file")
 */
class PublicFile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="file_size", type="integer", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(name="filename")
     */
    protected $filename;

    /**
     * @ORM\Column(name="hash_name")
     */
    protected $url;

    /**
     * @ORM\Column(name="directory_name")
     */
    protected $directoryName;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     */
    protected $creator;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="mime_type", nullable=true)
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="source_type", nullable=true)
     */
    protected $sourceType;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\File\PublicFile",
     *     mappedBy="publicFile"
     * )
     */
    protected $publicFileUses;

    public function __construct()
    {
        $this->publicFileUses = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getFormattedSize()
    {
        if ($this->size < 1024) {
            return $this->size.' B';
        } elseif ($this->size < 1048576) {
            return round($this->size / 1024, 2).' KB';
        } elseif ($this->size < 1073741824) {
            return round($this->size / 1048576, 2).' MB';
        } elseif ($this->size < 1099511627776) {
            return round($this->size / 1073741824, 2).' GB';
        }

        return round($this->size / 1099511627776, 2).' TB';
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getDirectoryName()
    {
        return $this->directoryName;
    }

    public function setDirectoryName($directoryName)
    {
        $this->directoryName = $directoryName;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getSourceType()
    {
        return $this->sourceType;
    }

    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;
    }

    public function getPublicFileUses()
    {
        return $this->publicFileUses->toArray();
    }
}
