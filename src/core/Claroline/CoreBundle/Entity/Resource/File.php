<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_file")
 */
class File extends AbstractResource
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $size;

    /**
     * @ORM\Column(name="hash_name", length=36, unique=true)
     */
    protected $hashName;

    /**
     * Returns the file size.
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets the file size.
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Returns the file size with unit and in a readable format.
     *
     * @return string
     */
    public function getFormattedSize()
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        } elseif ($this->size < 1048576) {
            return round($this->size / 1024, 2) . ' KB';
        } elseif ($this->size < 1073741824) {
            return round($this->size / 1048576, 2) . ' MB';
        } elseif ($this->size < 1099511627776) {
            return round($this->size / 1073741824, 2) . ' GB';
        }

        return round($this->size / 1099511627776, 2) . ' TB';
    }

    /**
     * Returns the name of the file actually stored in the file directory (as
     * opposed to the file original name, which is kept in the entity name
     * attribute).
     *
     * @return string
     */
    public function getHashName()
    {
        return $this->hashName;
    }

    /**
     * Sets the name of the physical file that will be stored in the file directory.
     * To prevent file name issues (e.g. with special characters), the original
     * file should be renamed with a standard unique identifier.
     *
     * @param string $hashName
     */
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }

}
